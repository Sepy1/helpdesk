<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Services\ExecutiveSummaryService;

class StatsController extends Controller
{
    /**
     * Mengembalikan data statistik dalam format JSON.
     * Query param:
     *  - month: 'all' atau 'YYYY-MM'
     */
    public function data(Request $request)
{
    Log::info('StatsController::data called', ['month' => $request->query('month')]);

    $month = $request->query('month', null);
    $dateFrom = $request->query('date_from');
    $dateTo   = $request->query('date_to');

    try {
        // base query (tickets)
        $tickets = \App\Models\Ticket::query();

        // filter by user (pembuat) jika diberikan
        if ($request->filled('user_id')) {
            $tickets->where('user_id', $request->query('user_id'));
        }

        // filter by date range if provided (date_from/date_to), else fallback to month format 'YYYY-MM'
        if ($dateFrom || $dateTo) {
            if ($dateFrom) {
                try { $df = Carbon::createFromFormat('Y-m-d', $dateFrom)->startOfDay(); } catch (\Exception $e) { return response()->json(['message' => 'Format date_from tidak valid'], 422); }
                $tickets->where('tickets.created_at', '>=', $df);
            }
            if ($dateTo) {
                try { $dt = Carbon::createFromFormat('Y-m-d', $dateTo)->endOfDay(); } catch (\Exception $e) { return response()->json(['message' => 'Format date_to tidak valid'], 422); }
                $tickets->where('tickets.created_at', '<=', $dt);
            }
        } elseif ($month) {
            try {
                $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
                $end   = (clone $start)->endOfMonth();
                $tickets->whereBetween('tickets.created_at', [$start, $end]);
            } catch (\Exception $e) {
                Log::warning('Invalid month format in stats request', ['month' => $month]);
                return response()->json(['message' => 'Format bulan tidak valid'], 422);
            }
        }

        //
        // KPI / Ringkasan
        // - total: semua tiket pada periode
        // - open: tiket yang belum closed (status != 'CLOSED')
        // - closed: tiket yang sudah closed (status == 'CLOSED')
        // - avg_resolution: rata-rata durasi (closed_at - created_at) dalam format manusia
        //
        $kpiTotal = (clone $tickets)->count();

        $kpiClosed = (clone $tickets)
            ->where('status', 'CLOSED')
            ->count();

        $kpiOpen = max(0, $kpiTotal - $kpiClosed); // fallback sederhana

        // rata-rata penyelesaian (detik) untuk tiket yang memiliki closed_at
        $avgSeconds = (clone $tickets)
            ->whereNotNull('closed_at')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(SECOND, tickets.created_at, tickets.closed_at)) as avg_seconds'))
            ->value('avg_seconds');

        // rata-rata response time (detik) dari created -> taken_at (ON_PROGRESS)
        $avgResponseSeconds = (clone $tickets)
            ->whereNotNull('taken_at')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(SECOND, tickets.created_at, tickets.taken_at)) as avg_response_seconds'))
            ->value('avg_response_seconds');

        // format human readable: jika null -> '-'
        $avgResolutionHuman = '-';
        $avgResolutionHours = null;

        if ($avgSeconds !== null) {
            // bisa jadi value string -> cast to int/float
            $avgSeconds = (float) $avgSeconds;
            $avgResolutionHours = $avgSeconds / 3600.0;

            if ($avgSeconds < 60) {
                $avgResolutionHuman = round($avgSeconds) . ' detik';
            } elseif ($avgSeconds < 3600) {
                $avgResolutionHuman = round($avgSeconds / 60) . ' menit';
            } elseif ($avgSeconds < 86400) {
                // jam
                $hours = floor($avgSeconds / 3600);
                $mins  = floor(($avgSeconds % 3600) / 60);
                $avgResolutionHuman = $hours . ' jam' . ($mins ? ' ' . $mins . ' mnt' : '');
            } else {
                // hari
                $days = floor($avgSeconds / 86400);
                $hours = floor(($avgSeconds % 86400) / 3600);
                $avgResolutionHuman = $days . ' hari' . ($hours ? ' ' . $hours . ' jam' : '');
            }
        }

        // format rata-rata response
        $avgResponseHuman = '-';
        $avgResponseHours = null;
        if ($avgResponseSeconds !== null) {
            $avgResponseSeconds = (float) $avgResponseSeconds;
            $avgResponseHours = $avgResponseSeconds / 3600.0;
            if ($avgResponseSeconds < 60) {
                $avgResponseHuman = round($avgResponseSeconds) . ' detik';
            } elseif ($avgResponseSeconds < 3600) {
                $avgResponseHuman = round($avgResponseSeconds / 60) . ' menit';
            } elseif ($avgResponseSeconds < 86400) {
                $hours = floor($avgResponseSeconds / 3600);
                $mins  = floor(($avgResponseSeconds % 3600) / 60);
                $avgResponseHuman = $hours . ' jam' . ($mins ? ' ' . $mins . ' mnt' : '');
            } else {
                $days = floor($avgResponseSeconds / 86400);
                $hours = floor(($avgResponseSeconds % 86400) / 3600);
                $avgResponseHuman = $days . ' hari' . ($hours ? ' ' . $hours . ' jam' : '');
            }
        }

        // ------------------------------
        // 1) Kategori terbanyak (join ke tabel categories)
        // ------------------------------
        $kategoriQuery = (clone $tickets)
            ->leftJoin('categories', 'tickets.category_id', '=', 'categories.id')
            ->select(DB::raw('COALESCE(categories.name, tickets.kategori, "Lainnya") as category_name'), DB::raw('count(tickets.id) as total'))
            ->groupBy('category_name')
            ->orderByDesc('total')
            ->limit(10);

        $kategori = $kategoriQuery->get();
        $kategoriLabels = $kategori->pluck('category_name')->all();
        $kategoriData   = $kategori->pluck('total')->all();

        // ------------------------------
        // 2) Status
        // ------------------------------
        $status = (clone $tickets)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        $statusLabels = $status->pluck('status')->all();
        $statusData   = $status->pluck('total')->all();

        // ------------------------------
        // 3) Top 5 user pembuat tiket (pakai user_id)
        // ------------------------------
        $top = (clone $tickets)
            ->select('user_id', DB::raw('count(*) as total'))
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $userIds = $top->pluck('user_id')->filter()->all();
        $users = \App\Models\User::whereIn('id', $userIds)->pluck('name','id');

        $topLabels = $top->map(fn($r) => $users[$r->user_id] ?? ('User #' . $r->user_id))->all();
        $topData   = $top->pluck('total')->all();

        // ------------------------------
        // 4) Root cause terbanyak
        // ------------------------------
        $root = (clone $tickets)
            ->select('root_cause', DB::raw('count(*) as total'))
            ->whereNotNull('root_cause')
            ->groupBy('root_cause')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $rootLabels = $root->pluck('root_cause')->map(fn($v) => $v ?? 'Tidak Ditentukan')->all();
        $rootData   = $root->pluck('total')->all();

        // kembalikan data JSON termasuk KPI
        return response()->json([
            'kpi' => [
                'total' => $kpiTotal,
                'open'  => $kpiOpen,
                'closed'=> $kpiClosed,
                // string yang ditampilkan di UI
                'avg_resolution' => $avgResolutionHuman,
                // tambahan numeric (jam) bila butuh export/komputasi lebih lanjut
                'avg_resolution_hours' => $avgResolutionHours !== null ? round($avgResolutionHours, 2) : null,
                'avg_response' => $avgResponseHuman,
                'avg_response_hours' => $avgResponseHours !== null ? round($avgResponseHours, 2) : null,
            ],
            'kategoriLabels' => $kategoriLabels,
            'kategoriData'   => $kategoriData,
            'statusLabels'   => $statusLabels,
            'statusData'     => $statusData,
            'topLabels'      => $topLabels,
            'topData'        => $topData,
            'rootLabels'     => $rootLabels,
            'rootData'       => $rootData,
        ]);
    } catch (\Throwable $e) {
        Log::error('StatsController::data error: '.$e->getMessage(), [
            'exception' => $e,
            'month' => $month,
        ]);

        if (app()->environment('local') || app()->environment('development')) {
            return response()->json(['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
        }

        return response()->json(['message' => 'Server error'], 500);
    }
}

    /** Generate PDF report for given date range */
    public function report(Request $request)
    {
        if (auth()->user()->role !== 'IT') abort(403);

        $dateFrom = $request->query('date_from');
        $dateTo   = $request->query('date_to');

        // reuse the data logic (basic filters)
        $ticketsBase = \App\Models\Ticket::query();
        if ($dateFrom) {
            try { $df = Carbon::createFromFormat('Y-m-d', $dateFrom)->startOfDay(); $ticketsBase->where('tickets.created_at', '>=', $df); } catch (\Exception $e) { /* ignore */ }
        }
        if ($dateTo) {
            try { $dt = Carbon::createFromFormat('Y-m-d', $dateTo)->endOfDay(); $ticketsBase->where('tickets.created_at', '<=', $dt); } catch (\Exception $e) { /* ignore */ }
        }

        // filter by user (pembuat) jika parameter disertakan
        if ($request->filled('user_id')) {
            $ticketsBase->where('user_id', $request->query('user_id'));
        }

        // KPIs
        $total = (clone $ticketsBase)->count();
        $closed = (clone $ticketsBase)->where('status', 'CLOSED')->count();
        $open = max(0, $total - $closed);

        $eskCount = (clone $ticketsBase)
            ->where(function($q){
                $q->where('status', 'ESKALASI_VENDOR')
                  ->orWhereHas('histories', fn($h) => $h->where('action', 'assigned_vendor'));
            })->count();

        $onProgress = (clone $ticketsBase)->where('status', 'ON_PROGRESS')->count();

        // root cause frequency
        $root = (clone $ticketsBase)
            ->select('root_cause', DB::raw('count(*) as total'))
            ->whereNotNull('root_cause')
            ->groupBy('root_cause')
            ->orderByDesc('total')
            ->get();

        $kategoriStats = (clone $ticketsBase)
            ->select('kategori', DB::raw('count(*) as total'))
            ->whereNotNull('kategori')
            ->groupBy('kategori')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'kategori' => $row->kategori,
                'jumlah' => (int) $row->total,
            ])
            ->values()
            ->all();

        $subKategoriStats = (clone $ticketsBase)
            ->leftJoin('subcategories', 'tickets.subcategory_id', '=', 'subcategories.id')
            ->select(DB::raw('COALESCE(subcategories.name, "Tidak Ditentukan") as sub_kategori'), DB::raw('count(tickets.id) as total'))
            ->groupBy('sub_kategori')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'sub_kategori' => $row->sub_kategori,
                'jumlah' => (int) $row->total,
            ])
            ->values()
            ->all();

        // tickets list (for table)
        $ticketsList = (clone $ticketsBase)
            ->with(['user','it','subcategory'])
            ->orderByDesc('created_at')
            ->get(['id','nomor_tiket','created_at','user_id','kategori','subcategory_id','root_cause','status','it_id','closed_at','closed_note','taken_at']);

        // eskalasi vendor tickets (separate list)
        $eskalasiTickets = (clone $ticketsBase)
            ->where(function($q){
                $q->where('status', 'ESKALASI_VENDOR')
                  ->orWhereHas('histories', fn($h) => $h->where('action', 'assigned_vendor'));
            })
            ->with(['user','it','subcategory'])
            ->orderByDesc('created_at')
            ->get(['id','nomor_tiket','created_at','user_id','kategori','subcategory_id','root_cause','status','it_id','closed_at','closed_note','taken_at']);

        // AI Executive Summary payload
        $ticketPayload = $ticketsList->map(function ($t) {
            $responseMinutes = null;
            if (!empty($t->taken_at) && !empty($t->created_at)) {
                $responseMinutes = $t->created_at->diffInMinutes($t->taken_at);
            }

            return [
                'pembuat_tiket' => optional($t->user)->name ?? 'tidak tersedia',
                'kategori_tiket' => $t->kategori ?? 'tidak tersedia',
                'sub_kategori_tiket' => optional($t->subcategory)->name ?? 'tidak tersedia',
                'closed_note' => $t->closed_note ?? 'tidak tersedia',
                'response_time_ti_menit' => $responseMinutes ?? 'tidak tersedia',
            ];
        })->values()->all();

        $summaryPayload = [
            'periode' => [
                'date_from' => $dateFrom ?? 'Semua',
                'date_to' => $dateTo ?? 'Semua',
            ],
            'statistik_jumlah_tiket' => [
                'total' => $total,
                'closed' => $closed,
                'open' => $open,
                'on_progress' => $onProgress,
                'eskalasi_vendor' => $eskCount,
            ],
            'statistik_kategori' => $kategoriStats,
            'statistik_sub_kategori' => $subKategoriStats,
            'data_tiket' => $ticketPayload,
        ];

        $executiveSummary = ExecutiveSummaryService::generate($summaryPayload);
        if (empty($executiveSummary)) {
            $executiveSummary = 'Ringkasan AI belum tersedia untuk laporan ini. Silakan cek konfigurasi OPENAI_API_KEY atau coba generate ulang.';
        }

        // prepare KPI chart via QuickChart (donut)
        $kpiLabels = ['Open','Closed','Eskalasi Vendor','On Progress'];
        $kpiValues = [$open, $closed, $eskCount, $onProgress];
        $kpiColors = ['#ef4444','#10b981','#8b5cf6','#f59e0b'];
        $chartConfig = [
            'type' => 'doughnut',
            'data' => [
                'labels' => $kpiLabels,
                'datasets' => [[
                    'data' => $kpiValues,
                    'backgroundColor' => $kpiColors,
                ]]
            ],
            'options' => [
                'plugins' => [
                    'legend' => ['display' => true, 'position' => 'bottom'],
                ],
            ],
        ];
        $kpiChartUrl = 'https://quickchart.io/chart?c=' . urlencode(json_encode($chartConfig));

        $html = view('it.stats_pdf', [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'kpi' => [ 'total'=>$total, 'open'=>$open, 'closed'=>$closed, 'eskalasi'=>$eskCount, 'on_progress'=>$onProgress ],
            'root' => $root,
            'tickets' => $ticketsList,
            'eskalasiTickets' => $eskalasiTickets,
            'kpiChartUrl' => $kpiChartUrl,
            'executiveSummary' => $executiveSummary,
        ])->render();

        // If Dompdf not installed, return HTML fallback so user can print to PDF from browser
        if (! class_exists('\Dompdf\\Dompdf')) {
            // include a small banner in the HTML to indicate server-side PDF not available
            $html = str_replace('<body>', '<body><div style="padding:8px;background:#fee;border:1px solid #fca;margin-bottom:10px;font-size:12px;">
                <strong>Server-side PDF generator tidak terpasang.</strong> Untuk mengunduh PDF dari server, jalankan: <code>composer require dompdf/dompdf</code> lalu coba lagi.
              </div>', $html);

            return response($html, 200, ['Content-Type' => 'text/html']);
        }

        // render PDF with Dompdf
            // render PDF with Dompdf (enable remote images for chart URLs)
            $options = new \Dompdf\Options();
            $options->set('isRemoteEnabled', true);
            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->loadHtml($html);
            $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="laporan_tiket_' . now()->format('Ymd_His') . '.pdf"'
        ]);
    }
}
