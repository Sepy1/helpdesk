<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Support\Facades\Mail;
use App\Mail\TicketSubmitted;
use Illuminate\Support\Facades\Schema;
use App\Mail\TicketClosed;
use Illuminate\Support\Facades\Log;
use App\Notifications\TicketActivity;


class TicketController extends Controller
{
    /** Sumber kebenaran kategori, status, root cause */
    public const KATEGORI    = ['JARINGAN','LAYANAN','CBS','OTHER'];
    public const STATUS      = ['OPEN','ON_PROGRESS','ESKALASI_VENDOR','VENDOR_RESOLVED','CLOSED'];
    public const ROOT_CAUSES = [
        'Human Error',
        'Pergantian User',
        'Penyesuaian Sistem',
        'Bug Sistem',
        'Kerusakan Hardware',
        'Kerusakan Software',
        'ISP Down',
        'Wireless Down',
        'Lainnya',
    ];

    /* =========================
     * UTIL
     * ========================= */
    /** Notify ticket participants (owner, IT, vendor) about a history change */
    protected function notifyHistory(\App\Models\Ticket $ticket, \App\Models\TicketHistory $history, string $title, ?string $body = null): void
    {
        try {
            $actor = auth()->user();
            $ticket->loadMissing(['user','it','vendor']);
            $recipients = collect([$ticket->user, $ticket->it, $ticket->vendor])
                ->filter()
                ->unique('id')
                ->reject(fn($u) => $actor && $u->id === $actor->id);

            $url = route('ticket.show', $ticket->id) . '#h-' . $history->id;
            $payload = [
                'ticket_id'   => $ticket->id,
                'ticket_no'   => $ticket->nomor_tiket ?? ('#'.$ticket->id),
                'kind'        => 'history',
                'title'       => $title,
                'body'        => $body,
                'url'         => $url,
                'actor_id'    => $actor?->id,
                'actor_name'  => $actor?->name,
                'history_id'  => $history->id,
                'action'      => $history->action,
                'created_at'  => now()->toIso8601String(),
            ];
            foreach ($recipients as $user) {
                $user->notify(new TicketActivity($payload));
            }
        } catch (\Throwable $e) {
            // ignore notification errors
        }
    }

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
    // Ambil kategori beserta subkategori (eager load).
    // Pastikan subcategory memuat category_id agar relasi terhubung saat eager load.
    $categories = Category::with(['subcategories' => function ($q) {
        $q->select('id', 'category_id', 'name')->orderBy('name');
    }])->orderBy('name')->get(['id', 'name']);

    return view('cabang.create_ticket', compact('categories'));
}

/** Simpan tiket baru */
public function store(Request $request)
{
    Log::info('TicketStore: masuk ke store() oleh user_id=' . optional(Auth::user())->id);

    // validasi
    $data = $request->validate([
        'category_id'   => 'required|exists:categories,id',
        'subcategory_id'=> 'nullable|exists:subcategories,id',
        'deskripsi'     => 'required|min:5',
        'lampiran'      => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx|max:5072',
    ], [], [
        'category_id' => 'kategori',
        'subcategory_id' => 'subkategori',
        'deskripsi' => 'deskripsi',
    ]);

    Log::info('TicketStore: validasi berhasil', ['input' => [
        'category_id' => $data['category_id'],
        'subcategory_id' => $data['subcategory_id'] ?? null,
    ]]);

    // pastikan subkategori memang milik kategori yang dipilih (jika ada)
    if (!empty($data['subcategory_id'])) {
        $ok = Subcategory::where('id', $data['subcategory_id'])
            ->where('category_id', $data['category_id'])
            ->exists();

        if (!$ok) {
            Log::warning('TicketStore: subkategori tidak valid', [
                'category_id' => $data['category_id'],
                'subcategory_id' => $data['subcategory_id']
            ]);

            return back()
                ->withErrors(['subcategory_id' => 'Subkategori tidak valid untuk kategori yang dipilih.'])
                ->withInput();
        }

        Log::info('TicketStore: subkategori valid', [
            'category_id' => $data['category_id'],
            'subcategory_id' => $data['subcategory_id']
        ]);
    }

    // simpan file jika ada
    $lampiranPath = $request->hasFile('lampiran')
        ? $request->file('lampiran')->store('lampiran', 'public')
        : null;

    if ($lampiranPath) {
        Log::info('TicketStore: lampiran disimpan', ['path' => $lampiranPath]);
    } else {
        Log::info('TicketStore: tidak ada lampiran di request');
    }

    // buat tiket dalam transaction
    try {
        $ticket = DB::transaction(function () use ($data, $lampiranPath) {
            $payload = [
                'nomor_tiket'    => $this->generateTicketNumber(),
                'user_id'        => auth()->id(),
                'category_id'    => $data['category_id'],
                'subcategory_id' => $data['subcategory_id'] ?? null,
                'deskripsi'      => $data['deskripsi'],
                'lampiran'       => $lampiranPath,
                'status'         => 'OPEN',
                'eskalasi'       => 'TIDAK',
            ];

            // backward-compat: jika masih ada kolom 'kategori', isi dengan nama kategori
            if (Schema::hasColumn('tickets', 'kategori')) {
                $cat = Category::find($data['category_id']);
                $payload['kategori'] = $cat ? $cat->name : null;
            }

            $created = Ticket::create($payload);

            Log::info('TicketStore: ticket dibuat di transaction', [
                'ticket_id' => $created->id,
                'nomor_tiket' => $created->nomor_tiket,
                'user_id' => $created->user_id,
            ]);

            // catat history: created
            \App\Models\TicketHistory::create([
                'ticket_id' => $created->id,
                'user_id'   => auth()->id(),
                'action'    => 'created',
                'note'      => 'Ticket dibuat',
                'meta'      => ['nomor_tiket' => $created->nomor_tiket],
            ]);
            return $created;
        });
    } catch (\Throwable $e) {
        Log::error('TicketStore: gagal membuat ticket', ['error' => $e->getMessage()]);
        return back()->with('error', 'Gagal membuat tiket. Silakan coba lagi.');
    }

    // kirim email notifikasi ke user (jika ada email)
    try {
        $user = $ticket->user ?? auth()->user();
        $recipientEmail = $user->email ?? null;

        Log::info('TicketStore: persiapan kirim email', [
            'ticket_id' => $ticket->id,
            'recipient' => $recipientEmail,
            'queue_default' => config('queue.default')
        ]);

        if (empty($recipientEmail)) {
            Log::warning('TicketStore: tidak ada email penerima, melewatkan pengiriman', [
                'ticket_id' => $ticket->id
            ]);
        } else {
            // Pilih metode pengiriman berdasarkan koneksi queue
            if (config('queue.default') === 'sync') {
                // sync = kirim langsung (berguna untuk debugging/lingkungan tanpa worker)
                Mail::to($recipientEmail)->send(new TicketSubmitted($ticket));
                Log::info('TicketStore: Mail::send() dipanggil (sync)', [
                    'ticket_id' => $ticket->id,
                    'recipient' => $recipientEmail
                ]);
            } else {
                // jika menggunakan queue (database/redis), queue job dan log bahwa job di-queue
                Mail::to($recipientEmail)->queue(new TicketSubmitted($ticket));
                Log::info('TicketStore: Mail::queue() dipanggil (queued)', [
                    'ticket_id' => $ticket->id,
                    'recipient' => $recipientEmail
                ]);
            }
        }
    } catch (\Throwable $e) {
        // log error tapi jangan lempar exception ke user
        Log::error("Gagal mengirim email tiket baru (ticket_id: {$ticket->id}): " . $e->getMessage(), [
            'ticket_id' => $ticket->id,
            'exception' => $e,
        ]);
    }

    // session flash untuk modal sukses & redirect
    session()->flash('new_ticket_no', $ticket->nomor_tiket ?? $ticket->id);
    session()->flash('new_ticket_id', $ticket->id);

    Log::info('TicketStore: selesai store(), redirect ke cabang.dashboard', [
        'ticket_id' => $ticket->id,
        'nomor_tiket' => $ticket->nomor_tiket ?? null
    ]);

    return redirect()
        ->route('cabang.dashboard')
        ->with('success', 'Tiket berhasil dibuat.');
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
    // Ambil daftar kategori untuk select filter
    $categories = Category::orderBy('name')->get(['id','name']);

    // Jika ada category_id di query, ambil subkategori terkait (untuk render awal)
    $selectedCategoryId = $request->query('category_id') ?? null;
    $subcategories = collect();
    if ($selectedCategoryId) {
        $subcategories = Subcategory::where('category_id', $selectedCategoryId)
                        ->orderBy('name')
                        ->get(['id','name']);
    }

    // Cek apakah tabel tickets sudah punya kolom category_id/subcategory_id
    $hasCategoryId = Schema::hasColumn('tickets', 'category_id');
    $hasSubcategoryId = Schema::hasColumn('tickets', 'subcategory_id');

    $tickets = Ticket::with(['user','it'])
        // filter status
        ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))

        // filter category: jika kolom category_id ada, pakai itu; jika belum, fallback ke legacy 'kategori'
        ->when($hasCategoryId && $request->filled('category_id'),
               fn($q) => $q->where('category_id', $request->category_id))
        ->when(!$hasCategoryId && $request->filled('kategori'),
               fn($q) => $q->where('kategori', $request->kategori))

        // filter subcategory jika kolom ada dan param disertakan
        ->when($hasSubcategoryId && $request->filled('subcategory_id'),
               fn($q) => $q->where('subcategory_id', $request->subcategory_id))

        // pencarian teks di nomor_tiket, deskripsi, atau kategori (legacy kolom)
        ->when($request->filled('q'), function ($q) use ($request) {
            $v = trim($request->q);
            $q->where(function ($qq) use ($v) {
                $qq->where('nomor_tiket', 'like', "%{$v}%")
                   ->orWhere('deskripsi', 'like', "%{$v}%")
                   ->orWhere('kategori', 'like', "%{$v}%");
            });
        })

        ->latest()
        ->paginate(10)
        ->withQueryString();

    // kirim semua data ke view agar select bisa di-render
    return view('it.dashboard', compact('tickets', 'categories', 'subcategories', 'selectedCategoryId'));
}

    /** Export daftar tiket sesuai filter ke CSV (dibuka Excel) */
    public function export(Request $request)
    {
        if (auth()->user()->role !== 'IT') abort(403);

        $hasCategoryId = Schema::hasColumn('tickets', 'category_id');
        $hasSubcategoryId = Schema::hasColumn('tickets', 'subcategory_id');

        $dateFrom = $request->query('date_from');
        $dateTo   = $request->query('date_to');

        $q = Ticket::with(['user','it'])
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($hasCategoryId && $request->filled('category_id'), fn($q) => $q->where('category_id', $request->category_id))
            ->when(!$hasCategoryId && $request->filled('kategori'), fn($q) => $q->where('kategori', $request->kategori))
            ->when($hasSubcategoryId && $request->filled('subcategory_id'), fn($q) => $q->where('subcategory_id', $request->subcategory_id))
            ->when($request->filled('q'), function ($q) use ($request) {
                $v = trim($request->q);
                $q->where(function ($qq) use ($v) {
                    $qq->where('nomor_tiket', 'like', "%{$v}%")
                       ->orWhere('deskripsi', 'like', "%{$v}%")
                       ->orWhere('kategori', 'like', "%{$v}%");
                });
            })
            ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->orderByDesc('created_at');

        $rows = $q->get();

        $filename = 'tickets_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function() use ($rows) {
            $out = fopen('php://output', 'w');
            // BOM for Excel UTF-8
            echo "\xEF\xBB\xBF";
            // Header
            fputcsv($out, ['Nomor', 'Kategori', 'Pembuat', 'IT Handler', 'Status', 'Dibuat', 'Deskripsi'], ';');
            foreach ($rows as $t) {
                fputcsv($out, [
                    $t->nomor_tiket,
                    $t->kategori,
                    optional($t->user)->name,
                    optional($t->it)->name,
                    $t->status,
                    optional($t->created_at)?->format('d M Y H:i'),
                    $t->deskripsi,
                ], ';');
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    /** Tiket yang sedang diambil alih oleh IT ini + filter */
    public function myAssigned(Request $request)
{
    if (Auth::user()->role !== 'IT') abort(403);

    // Ambil semua kategori untuk dropdown (urutkan bila perlu)
    $categories = \App\Models\Category::orderBy('name')->get();

    // ambil nilai terpilih dari query string (bisa datang sebagai category_id atau kategori)
    $selectedCategoryId = $request->query('category_id');
    $selectedSubcategoryId = $request->query('subcategory_id');

    // Siapkan koleksi subkategori (kosong default)
    $subcategories = collect();
    if ($selectedCategoryId) {
        // jika ada relationship Category->subcategories
        $cat = \App\Models\Category::with('subcategories')->find($selectedCategoryId);
        if ($cat) {
            $subcategories = $cat->subcategories;
        } else {
            // fallback: coba ambil dari tabel subcategories jika model berbeda
            $subcategories = \App\Models\Subcategory::where('category_id', $selectedCategoryId)->get();
        }
    }


    $tickets = Ticket::with(['user', 'it'])
        ->where('it_id', Auth::id())
        // status filter (sama seperti Anda)
        ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
        // kategori: dukung dua kemungkinan param (category_id dari form baru, atau kategori lama)
        ->when($request->filled('category_id'), fn ($q) => $q->where('kategori', $request->category_id))
        ->when($request->filled('kategori') && !$request->filled('category_id'), fn ($q) => $q->where('kategori', $request->kategori))
        // subkategori (jika Anda menyimpan subcategory_id di tabel tickets)
        ->when($request->filled('subcategory_id'), fn ($q) => $q->where('subcategory_id', $request->subcategory_id))
        // pencarian q (nomor/deskripsi/kategori)
        ->when($request->filled('q'), function ($q) use ($request) {
            $v = trim($request->q);
            $q->where(function ($qq) use ($v) {
                $qq->where('nomor_tiket', 'like', "%$v%")
                   ->orWhere('deskripsi', 'like', "%$v%")
                   ->orWhere('kategori', 'like', "%$v%");
            });
        })
        ->latest()
        ->paginate(10)
        ->withQueryString();

    // kirim juga variabel kategori/subkategori & selected id ke view
    return view('it.my', compact('tickets', 'categories', 'subcategories', 'selectedCategoryId', 'selectedSubcategoryId'));
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
            'taken_at' => $ticket->taken_at ?: now(),
        ]);

        // history
        $h = \App\Models\TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id'   => Auth::id(),
            'action'    => 'taken',
            'note'      => 'Tiket diambil oleh IT',
        ]);
        $this->notifyHistory($ticket, $h, 'Tiket diambil oleh IT', 'Tiket sedang ditangani.');
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

        $h = \App\Models\TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id'   => Auth::id(),
            'action'    => 'released',
            'note'      => 'Tiket dilepas ke antrian',
        ]);
        $this->notifyHistory($ticket, $h, 'Tiket dilepas ke antrian', 'Status kembali OPEN.');
        return back()->with('success', 'Tiket dilepas kembali ke antrian.');
    }

    /** IT menutup tiket (wajib root cause + catatan penyelesaian) */
  public function close(Request $request, Ticket $ticket)
{
    Log::info('TicketClose: masuk ke close() oleh user_id=' . optional(Auth::user())->id, [
        'ticket_id' => $ticket->id
    ]);

    if (Auth::user()->role !== 'IT') {
        Log::warning('TicketClose: akses ditolak, bukan IT user', [
            'user_id' => optional(Auth::user())->id,
            'user_role' => Auth::user()->role ?? null,
            'ticket_id' => $ticket->id
        ]);
        abort(403);
    }

    $data = $request->validate([
        'root_cause'  => 'required|string|in:' . implode(',', self::ROOT_CAUSES),
        'closed_note' => 'required|string|min:3',
    ]);

    Log::info('TicketClose: validasi berhasil', [
        'ticket_id' => $ticket->id,
        'root_cause' => $data['root_cause']
    ]);

    try {
        $ticket->update([
            'status'      => 'CLOSED',
            'it_id'       => $ticket->it_id ?: Auth::id(),
            'closed_at'   => now(),
            'root_cause'  => $data['root_cause'],
            'closed_note' => $data['closed_note'],
        ]);

        // history close
        $h = \App\Models\TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id'   => Auth::id(),
            'action'    => 'closed',
            'note'      => $data['closed_note'],
            'meta'      => ['root_cause' => $data['root_cause']],
        ]);
        $this->notifyHistory($ticket, $h, 'Tiket ditutup', $data['closed_note']);

        Log::info('TicketClose: ticket diupdate', [
            'ticket_id' => $ticket->id,
            'it_id' => $ticket->it_id,
            'closed_at' => $ticket->closed_at,
        ]);
    } catch (\Throwable $e) {
        Log::error('TicketClose: gagal update tiket', [
            'ticket_id' => $ticket->id,
            'exception' => $e->getMessage()
        ]);
        return back()->with('error', 'Gagal menutup tiket. Silakan coba lagi.');
    }

    // Kirim notifikasi email ke pemilik tiket (jika ada)
    try {
        // prioritas: relationship user -> email, fallback ke kolom email di tabel tickets (jika ada)
        $recipientEmail = optional($ticket->user)->email ?? ($ticket->email ?? null);

        Log::info('TicketClose: persiapan kirim email', [
            'ticket_id' => $ticket->id,
            'recipient' => $recipientEmail,
            'queue_default' => config('queue.default')
        ]);

        if (empty($recipientEmail)) {
            Log::warning('TicketClose: tidak ada email penerima, melewatkan pengiriman', [
                'ticket_id' => $ticket->id
            ]);
        } else {
            if (config('queue.default') === 'sync') {
                // kirim langsung (sinkron)
                Mail::to($recipientEmail)->send(new \App\Mail\TicketClosed($ticket));
                Log::info('TicketClose: Mail::send() dipanggil (sync)', [
                    'ticket_id' => $ticket->id,
                    'recipient' => $recipientEmail
                ]);
            } else {
                // gunakan queue (butuh queue worker berjalan)
                Mail::to($recipientEmail)->queue(new \App\Mail\TicketClosed($ticket));
                Log::info('TicketClose: Mail::queue() dipanggil (queued)', [
                    'ticket_id' => $ticket->id,
                    'recipient' => $recipientEmail
                ]);
            }
        }
    } catch (\Throwable $e) {
        // Log error agar tidak mengganggu UX
        Log::error("Gagal mengirim email tiket closed (ticket_id: {$ticket->id}): " . $e->getMessage(), [
            'ticket_id' => $ticket->id,
            'exception' => $e
        ]);
    }

    Log::info('TicketClose: selesai close(), kembali ke halaman sebelumnya', [
        'ticket_id' => $ticket->id
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
            // Jika ingin reset juga waktu take:
            // 'taken_at'  => null,
        ]);

        $h = \App\Models\TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id'   => Auth::id(),
            'action'    => 'reopened',
            'note'      => 'Tiket dibuka kembali',
        ]);
        $this->notifyHistory($ticket, $h, 'Tiket dibuka kembali', 'Status kembali OPEN.');
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

    /** Simpan tindakan progress IT (+timestamp progress_at) */
    public function saveProgress(Request $request, Ticket $ticket)
    {
        if (Auth::user()->role !== 'IT') abort(403);

        $data = $request->validate([
            'progress_note' => 'required|string|min:3',
        ]);

        if ($ticket->status === 'OPEN') {
            $ticket->status   = 'ON_PROGRESS';
            $ticket->it_id    = $ticket->it_id ?: Auth::id();
            $ticket->taken_at = $ticket->taken_at ?: now();
        }

        $ticket->progress_note = $data['progress_note'];
        $ticket->progress_at   = now();
        $ticket->save();

        $h = \App\Models\TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id'   => Auth::id(),
            'action'    => 'progress',
            'note'      => $data['progress_note'],
        ]);
        $this->notifyHistory($ticket, $h, 'Progres IT', $data['progress_note']);
        return back()->with('success', 'Tindakan progress disimpan.');
    }

    /** Simpan tindak lanjut dari vendor (+timestamp)
     *  - IT boleh mengisi
     *  - VENDOR boleh mengisi hanya jika ticket.vendor_id == auth()->id()
     */
    public function vendorFollowup(Request $request, Ticket $ticket)
    {
        $user = Auth::user();
        if (! $user) abort(401);

        $isIT = $user->role === 'IT';
        $isVendorOwner = $user->role === 'VENDOR' && (int)$ticket->vendor_id === (int)$user->id;
        if (!($isIT || $isVendorOwner)) {
            abort(403);
        }

        $request->validate([
            'vendor_followup' => 'required|string|min:3',
        ]);

        // Simpan tindak lanjut vendor
        $ticket->vendor_followup    = $request->vendor_followup;
        $ticket->vendor_followup_at = now();

        // Jika IT yang menyimpan, sekaligus tutup tiket (sesuai permintaan)
        if ($isIT) {
            $ticket->status    = 'CLOSED';
            $ticket->it_id     = $ticket->it_id ?: $user->id;
            $ticket->closed_at = now();
        } else {
            // Jika vendor yang mengisi, tandai sebagai vendor resolved
            $ticket->status = 'VENDOR_RESOLVED';
        }

        $ticket->save();

        // history vendor follow-up
        $h = \App\Models\TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
            'action'    => 'vendor_followup',
            'note'      => $request->vendor_followup,
            'meta'      => ['closed_by_it' => $isIT],
        ]);
        $this->notifyHistory($ticket, $h, $isIT ? 'Vendor follow-up (ditutup IT)' : 'Vendor follow-up', $request->vendor_followup);

        return back()->with('success', $isIT ? 'Tindak lanjut disimpan dan tiket ditutup.' : 'Tindak lanjut vendor disimpan.');
    }

    /** Assign ticket ke vendor (role VENDOR) */
    public function assignVendor(Request $request, Ticket $ticket)
    {
        if (Auth::user()->role !== 'IT') abort(403);

        $data = $request->validate([
            // vendor bisa dikosongkan (opsi "Tidak")
            'vendor_id' => ['nullable','integer','exists:users,id'],
        ]);

        // jika kosong, hapus assignment vendor
        if (empty($data['vendor_id'])) {
            $ticket->vendor_id = null;
            if (Schema::hasColumn('tickets','eskalasi')) {
                $ticket->eskalasi = 'TIDAK';
            }
            $ticket->save();
            $h = \App\Models\TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id'   => Auth::id(),
                'action'    => 'assign_vendor_cleared',
                'note'      => 'Assignment vendor dihapus',
            ]);
            $this->notifyHistory($ticket, $h, 'Vendor dihapus dari tiket');
            return back()->with('success', 'Vendor dihapus dari tiket.');
        }

        // pastikan user yang dipilih adalah VENDOR
        $vendor = User::where('id', $data['vendor_id'])->where('role', 'VENDOR')->first();
        if (! $vendor) {
            return back()->withErrors(['vendor_id' => 'Pilih vendor yang valid.'])->withInput();
        }

        $ticket->update([
            'vendor_id' => $vendor->id,
            // kompatibilitas lama: set eskalasi VENDOR bila kolom ada
            'eskalasi'  => Schema::hasColumn('tickets','eskalasi') ? 'VENDOR' : $ticket->eskalasi,
            // set status khusus agar terlihat di UI sebagai eskalasi ke vendor
            'status'    => 'ESKALASI_VENDOR',
        ]);

        // history assign vendor
        $h = \App\Models\TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id'   => Auth::id(),
            'action'    => 'assigned_vendor',
            'note'      => 'Assign ke vendor: ' . ($vendor->name ?? ('ID '.$vendor->id)),
            'meta'      => ['vendor_id' => $vendor->id, 'vendor_name' => $vendor->name],
        ]);
        $this->notifyHistory($ticket, $h, 'Assign ke vendor', 'Ditugaskan ke '.$vendor->name);

        return back()->with('success', 'Vendor berhasil diassign.');
    }

    /* =========================
     * DETAIL, KOMENTAR & LAMPIRAN
     * ========================= */

    /** Detail tiket (IT & cabang-yang-bersangkutan) */
    public function show(Ticket $ticket)
    {
        $ticket->load(['user', 'it', 'vendor', 'comments.user', 'histories.user']);

        if (Auth::user()->role === 'CABANG' && $ticket->user_id !== Auth::id()) {
            abort(403);
        }

        $vendors = User::where('role','VENDOR')->orderBy('name')->get(['id','name']);
        return view('tickets.show', compact('ticket','vendors'));
    }

    /** Tambah komentar */
    public function comment(Request $request, Ticket $ticket)
    {
        
        $request->validate([
        'body' => 'required|string',
        'attachment' => 'nullable|file|max:5048',
    ]);

    $data = [
        'ticket_id' => $ticket->id,
        'user_id' => auth()->id(),
        'body' => $request->body,
    ];

    if ($request->hasFile('attachment')) {
        $data['attachment'] = $request->file('attachment')->store('attachments', 'public');
    }

    \App\Models\TicketComment::create($data);

    return back()->with('success','Komentar berhasil ditambahkan');
    }

    /** Hapus komentar (pemilik komentar atau IT) */
    public function deleteComment(TicketComment $comment)
    {
        // Hanya pemilik komentar yang boleh menghapus, dan tidak boleh jika tiket CLOSED
        $user = Auth::user();
        if (! $user) abort(401);
        $ticket = $comment->ticket;
        if ($ticket && $ticket->status === 'CLOSED') {
            abort(403);
        }
        $canDelete = $user->id === $comment->user_id;
        if (! $canDelete) abort(403);

        $comment->delete();
        return back()->with('success', 'Komentar dihapus.');
    }

    public function storeComment(Request $request, Ticket $ticket)
{
    $request->validate([
        'comment' => 'required|string',
        'attachment' => 'nullable|file|max:2048', // max 2MB
    ]);

    $path = null;
    if ($request->hasFile('attachment')) {
        $path = $request->file('attachment')->store('attachments', 'public');
    }

    $ticket->comments()->create([
        'user_id' => auth()->id(),
        'comment' => $request->comment,
        'attachment' => $path,
    ]);

    return back()->with('success', 'Komentar berhasil ditambahkan.');
}



// Unduh lampiran komentar comment tiket 
public function downloadCommentAttachment(TicketComment $comment)
{
    // otorisasi: pemilik komentar, pemilik tiket, atau IT
    $user = auth()->user();
    $isOwnerComment = $user && $user->id === $comment->user_id;
    $isTicketOwner  = $user && $user->id === $comment->ticket->user_id;
    $isIT           = $user && $user->role === 'IT';

    if (! ($isOwnerComment || $isTicketOwner || $isIT)) {
        abort(403);
    }

    if (! $comment->attachment) {
        abort(404);
    }

    // file tersimpan di disk 'public'
    return Storage::disk('public')->download($comment->attachment);
}


    /** Unduh lampiran tiket */
    public function downloadAttachment(Ticket $ticket)
    {
        if (! $ticket->lampiran) abort(404);

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

        // Status (OPEN, ON_PROGRESS, CLOSED) — default 0 bila tidak ada
        $qStatus = Ticket::select('status', DB::raw('COUNT(*) AS total'))
            ->groupBy('status')
            ->get()
            ->keyBy('status');
        $statusLabels = self::STATUS;
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

        return view('it.stats', [
            'kategoriLabels' => $kategoriLabels,
            'kategoriData'   => $kategoriData,
            'statusLabels'   => $statusLabels,
            'statusData'     => $statusData,
            'topLabels'      => $topLabels,
            'topData'        => $topData,
        ]);
    }



public function subcategories($id)
{
    // pastikan kategori ada
    $category = Category::find($id);
    if (!$category) {
        return response()->json([], 404);
    }

    $subs = Subcategory::where('category_id', $id)
            ->select('id','name')
            ->orderBy('name')
            ->get();

    return response()->json($subs);
}

    /* =========================
     * VENDOR
     * ========================= */
    /** Daftar tiket yang di-assign ke vendor ini */
    public function vendorTickets(Request $request)
    {
        if (auth()->user()->role !== 'VENDOR') abort(403);

        $tickets = Ticket::with(['user','it','vendor'])
            ->where('vendor_id', auth()->id())
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
            ->appends($request->query());

        return view('vendor.my_tickets', [
            'tickets'  => $tickets,
            'kategori' => self::KATEGORI,
            'status'   => self::STATUS,
        ]);
    }


}
