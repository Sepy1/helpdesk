<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    /** Sumber kebenaran kategori & status */
    public const KATEGORI = ['JARINGAN','LAYANAN','CBS','OTHER'];
    public const STATUS   = ['OPEN','ON_PROGRESS','CLOSED'];

    /* =========================
     * UTIL
     * ========================= */

    /** Generate nomor tiket unik: TCK-YYYYMM-XXXX (atomic dgn transaksi) */
    private function generateTicketNumber(): string
    {
        $prefix = 'TCK-' . now()->format('Ym') . '-';

        return DB::transaction(function () use ($prefix) {
            $last = DB::table('tickets')
                ->where('nomor_tiket', 'like', $prefix.'%')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->value('nomor_tiket');

            $seq = $last ? ((int) substr($last, -4) + 1) : 1;
            return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
        });
    }

    /* =========================
     * CABANG
     * ========================= */

    /** Form buat tiket */
    public function create()
    {
        return view('cabang.create_ticket', ['kategori' => self::KATEGORI]);
    }

    /** Simpan tiket baru */
    public function store(Request $request)
    {
        $request->validate([
            'kategori'  => 'required|in:' . implode(',', self::KATEGORI),
            'deskripsi' => 'required|min:5',
            'lampiran'  => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx|max:5072',
        ]);

        $lampiran = $request->hasFile('lampiran')
            ? $request->file('lampiran')->store('lampiran', 'public')
            : null;

        $ticket = DB::transaction(function () use ($request, $lampiran) {
            return Ticket::create([
                'nomor_tiket' => $this->generateTicketNumber(),
                'user_id'     => auth()->id(),
                'kategori'    => $request->kategori,
                'deskripsi'   => $request->deskripsi,
                'lampiran'    => $lampiran,
                'status'      => 'OPEN',
                // default untuk kolom baru (kalau ada)
                'eskalasi'    => 'TIDAK',
            ]);
        });

        return redirect()
            ->route('cabang.dashboard')
            ->with('success', 'Tiket berhasil dibuat.')
            ->with('new_ticket_no', $ticket->nomor_tiket)
            ->with('new_ticket_id', $ticket->id);
    }

    /** Daftar tiket milik user cabang (+filter) */
    public function myTickets(Request $request)
    {
        $tickets = Ticket::where('user_id', Auth::id())
            ->when($request->filled('status'),   fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('kategori'), fn ($q) => $q->where('kategori', $request->kategori))
            ->when($request->filled('q'), function ($q) use ($request) {
                $v = trim($request->q);
                $q->where(function ($qq) use ($v) {
                    $qq->where('nomor_tiket', 'like', "%$v%")
                       ->orWhere('deskripsi', 'like', "%$v%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('cabang.my_tickets', [
            'tickets'  => $tickets,
            'kategori' => self::KATEGORI,
            'status'   => self::STATUS,
        ]);
    }

    /* =========================
     * IT
     * ========================= */

    /** Dashboard IT: list tiket + filter */
    public function index(Request $request)
    {
        $tickets = Ticket::with(['user', 'it'])
            ->when($request->filled('status'),   fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('kategori'), fn ($q) => $q->where('kategori', $request->kategori))
            ->when($request->filled('q'), function ($q) use ($request) {
                $v = trim($request->q);
                $q->where(function ($qq) use ($v) {
                    $qq->where('nomor_tiket', 'like', "%$v%")
                       ->orWhere('deskripsi', 'like', "%$v%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('it.dashboard', compact('tickets'));
    }

    /** Tiket yang sedang diambil alih oleh IT ini + filter */
    public function myAssigned(Request $request)
    {
        if (Auth::user()->role !== 'IT') abort(403);

        $tickets = Ticket::with(['user', 'it'])
            ->where('it_id', Auth::id())
            ->when($request->filled('status'),   fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('kategori'), fn ($q) => $q->where('kategori', $request->kategori))
            ->when($request->filled('q'), function ($q) use ($request) {
                $v = trim($request->q);
                $q->where(function ($qq) use ($v) {
                    $qq->where('nomor_tiket', 'like', "%$v%")
                       ->orWhere('deskripsi', 'like', "%$v%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('it.my', compact('tickets'));
    }

    /** IT mengambil alih tiket */
    public function take(Ticket $ticket)
    {
        if (Auth::user()->role !== 'IT') abort(403);
        if ($ticket->status === 'CLOSED') {
            return back()->with('error', 'Tiket sudah ditutup.');
        }
        if ($ticket->status !== 'OPEN' && $ticket->it_id && $ticket->it_id !== Auth::id()) {
            return back()->with('error', 'Tiket sudah ditangani orang lain.');
        }

        $ticket->update([
            'status'   => 'ON_PROGRESS',
            'it_id'    => Auth::id(),
            'taken_at' => $ticket->taken_at ?: now(), // set sekali saat pertama di-take
        ]);

        return back()->with('success', 'Tiket diambil.');
    }

    /** IT melepaskan tiket yang sedang di-handle (kembali OPEN) */
    public function release(Ticket $ticket)
    {
        if (Auth::user()->role !== 'IT' || $ticket->it_id !== Auth::id()) abort(403);

        if ($ticket->status !== 'ON_PROGRESS') {
            return back()->with('error', 'Hanya tiket ON_PROGRESS yang bisa dilepas.');
        }

        $ticket->update([
            'status' => 'OPEN',
            'it_id'  => null,
        ]);

        return back()->with('success', 'Tiket dilepas kembali ke antrian.');
    }

    /** IT menutup tiket (wajib catatan tindak lanjut) */
    public function close(Request $request, Ticket $ticket)
    {
        if (Auth::user()->role !== 'IT') abort(403);

        $request->validate([
            'closed_note' => 'required|string|min:3',
        ]);

        $ticket->update([
            'status'      => 'CLOSED',
            'it_id'       => $ticket->it_id ?: Auth::id(),
            'closed_at'   => now(),
            'closed_note' => $request->closed_note,
        ]);

        return back()->with('success', 'Tiket ditutup.');
    }

    /** Re-open tiket (IT) -> kembali OPEN & tanpa handler */
    public function reopen(Ticket $ticket)
    {
        if (Auth::user()->role !== 'IT') abort(403);

        $ticket->update([
            'status'    => 'OPEN',
            'it_id'     => null,
            'closed_at' => null,
            // 'taken_at' => null, // jika ingin reset juga
        ]);

        return back()->with('success', 'Tiket dibuka kembali.');
    }

    /** Set eskalasi (VENDOR/TIDAK) */
    public function setEskalasi(Request $request, Ticket $ticket)
    {
        if (Auth::user()->role !== 'IT') abort(403);

        $data = $request->validate([
            'eskalasi' => 'required|in:VENDOR,TIDAK',
        ]);

        $ticket->update(['eskalasi' => $data['eskalasi']]);

        return back()->with('success', 'Eskalasi tersimpan.');
    }

      /** Save Progress */
    public function saveProgress(Request $request, Ticket $ticket)
{
    if (Auth::user()->role !== 'IT') abort(403);

    $data = $request->validate([
        'progress_note' => 'required|string|min:3',
    ]);

    // Set status ON_PROGRESS jika belum
    if ($ticket->status === 'OPEN') {
        $ticket->status   = 'ON_PROGRESS';
        $ticket->it_id    = $ticket->it_id ?: Auth::id();
        $ticket->taken_at = $ticket->taken_at ?: now();
    }

    $ticket->progress_note = $data['progress_note'];
    $ticket->progress_at   = now();
    $ticket->save();

    return back()->with('success', 'Tindakan progress disimpan.');
}



    /** Simpan tindak lanjut dari vendor + timestamp */
    public function vendorFollowup(Request $request, Ticket $ticket)
    {
        if (Auth::user()->role !== 'IT') abort(403);

        $request->validate([
            'vendor_followup' => 'required|string|min:3',
        ]);

        $ticket->update([
            'vendor_followup'    => $request->vendor_followup,
            'vendor_followup_at' => now(),
            // pastikan flag eskalasi menjadi VENDOR
            'eskalasi'           => 'VENDOR',
        ]);

        return back()->with('success', 'Tindak lanjut vendor disimpan.');
    }

    /* =========================
     * DETAIL, KOMENTAR & LAMPIRAN
     * ========================= */

    /** Detail tiket (IT & cabang-yang-bersangkutan) */
    public function show(Ticket $ticket)
    {
        $ticket->load(['user', 'it', 'comments.user']);

        if (Auth::user()->role === 'CABANG' && $ticket->user_id !== Auth::id()) {
            abort(403);
        }

        return view('tickets.show', compact('ticket'));
    }

    /** Tambah komentar/progres */
    public function comment(Request $request, Ticket $ticket)
    {
        if (Auth::user()->role === 'CABANG' && $ticket->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate(['body' => 'required|min:2']);

        TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id'   => Auth::id(),
            'body'      => $request->body,
        ]);

        return back()->with('success', 'Komentar ditambahkan.');
    }

    /** Hapus komentar (pemilik komentar atau IT) */
    public function deleteComment(TicketComment $comment)
    {
        $canDelete = Auth::id() === $comment->user_id || Auth::user()->role === 'IT';
        if (! $canDelete) abort(403);

        $comment->delete();
        return back()->with('success', 'Komentar dihapus.');
    }

    /** Unduh lampiran tiket */
    public function downloadAttachment(Ticket $ticket)
    {
        if (! $ticket->lampiran) abort(404);

        // Cabang hanya boleh unduh lampiran miliknya
        if (Auth::user()->role === 'CABANG' && $ticket->user_id !== Auth::id()) {
            abort(403);
        }

        return Storage::disk('public')->download($ticket->lampiran);
    }

    /* =========================
     * STATISTIK IT
     * ========================= */

    /** Statistik untuk IT: kategori, status, top 5 user */
    public function stats()
    {
        if (auth()->user()->role !== 'IT') abort(403);

        // Kategori
        $qKategori = Ticket::select('kategori', DB::raw('COUNT(*) AS total'))
            ->groupBy('kategori')
            ->orderByDesc('total')
            ->get();
        $kategoriLabels = $qKategori->pluck('kategori');
        $kategoriData   = $qKategori->pluck('total');

        // Status (pastikan urutan & default 0)
        $qStatus = Ticket::select('status', DB::raw('COUNT(*) AS total'))
            ->groupBy('status')
            ->get()
            ->keyBy('status');
        $statusLabels = ['OPEN', 'ON_PROGRESS', 'CLOSED'];
        $statusData   = collect($statusLabels)->map(fn ($s) => (int) optional($qStatus->get($s))->total)->values();

        // Top 5 user pembuat
        $topUsers = User::select('users.id', 'users.name', DB::raw('COUNT(tickets.id) AS total'))
            ->join('tickets', 'tickets.user_id', '=', 'users.id')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();
        $topLabels = $topUsers->pluck('name');
        $topData   = $topUsers->pluck('total');

        return view('it.stats', compact(
            'kategoriLabels','kategoriData',
            'statusLabels','statusData',
            'topLabels','topData'
        ));
    }
}
