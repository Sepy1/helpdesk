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
use Illuminate\Support\Facades\Schema;

class TicketController extends Controller
{
    /** Sumber kebenaran kategori, status, root cause */
    public const KATEGORI    = ['JARINGAN','LAYANAN','CBS','OTHER'];
    public const STATUS      = ['OPEN','ON_PROGRESS','CLOSED'];
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

    // pastikan subkategori memang milik kategori yang dipilih (jika ada)
    if (!empty($data['subcategory_id'])) {
        $ok = Subcategory::where('id', $data['subcategory_id'])
            ->where('category_id', $data['category_id'])
            ->exists();

        if (!$ok) {
            return back()
                ->withErrors(['subcategory_id' => 'Subkategori tidak valid untuk kategori yang dipilih.'])
                ->withInput();
        }
    }

    // simpan file jika ada
    $lampiranPath = $request->hasFile('lampiran')
        ? $request->file('lampiran')->store('lampiran', 'public')
        : null;

    // buat tiket dalam transaction
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

        return Ticket::create($payload);
    });

    // session flash untuk modal sukses & redirect
    session()->flash('new_ticket_no', $ticket->nomor_tiket ?? $ticket->id);
    session()->flash('new_ticket_id', $ticket->id);

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

        // pencarian teks di nomor_tiket atau deskripsi
        ->when($request->filled('q'), function ($q) use ($request) {
            $v = trim($request->q);
            $q->where(function ($qq) use ($v) {
                $qq->where('nomor_tiket', 'like', "%{$v}%")
                   ->orWhere('deskripsi', 'like', "%{$v}%");
            });
        })

        ->latest()
        ->paginate(10)
        ->withQueryString();

    // kirim semua data ke view agar select bisa di-render
    return view('it.dashboard', compact('tickets', 'categories', 'subcategories', 'selectedCategoryId'));
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
        // pencarian q (nomor/deskripsi)
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

    /** IT menutup tiket (wajib root cause + catatan penyelesaian) */
    public function close(Request $request, Ticket $ticket)
    {
        if (Auth::user()->role !== 'IT') abort(403);

        $data = $request->validate([
            'root_cause'  => 'required|string|in:' . implode(',', self::ROOT_CAUSES),
            'closed_note' => 'required|string|min:3',
        ]);

        $ticket->update([
            'status'      => 'CLOSED',
            'it_id'       => $ticket->it_id ?: Auth::id(),
            'closed_at'   => now(),
            'root_cause'  => $data['root_cause'],
            'closed_note' => $data['closed_note'],
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

        return back()->with('success', 'Tindakan progress disimpan.');
    }

    /** Simpan tindak lanjut dari vendor (+timestamp) */
    public function vendorFollowup(Request $request, Ticket $ticket)
    {
        if (Auth::user()->role !== 'IT') abort(403);

        $request->validate([
            'vendor_followup' => 'required|string|min:3',
        ]);

        $ticket->update([
            'vendor_followup'    => $request->vendor_followup,
            'vendor_followup_at' => now(),
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
        $canDelete = Auth::id() === $comment->user_id || Auth::user()->role === 'IT';
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


}
