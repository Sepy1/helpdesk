<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\User;
use App\Models\KodeKantor;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Services\ExecutiveSummaryService;

class StatsController extends Controller
{
    /** Filter tiket berdasarkan kode kantor pembuat (users.kode_kantor). */
    protected function applyKantorFilterToTickets(\Illuminate\Database\Eloquent\Builder $tickets, Request $request): void
    {
        if (! $request->filled('kode_kantor')) {
            return;
        }
        $kode = trim((string) $request->input('kode_kantor', $request->query('kode_kantor')));
        $tickets->whereHas('user', fn ($uq) => $uq->where('kode_kantor', $kode));
    }

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

        $this->applyKantorFilterToTickets($tickets, $request);

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
        // 3) Top 5 kantor pembuat (users.kode_kantor)
        // ------------------------------
        $top = (clone $tickets)
            ->join('users', 'tickets.user_id', '=', 'users.id')
            ->select('users.kode_kantor', DB::raw('count(tickets.id) as total'))
            ->groupBy('users.kode_kantor')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $kodeList = $top->pluck('kode_kantor')->filter()->values();
        $namaByKode = KodeKantor::whereIn('kode', $kodeList)->pluck('nama_kantor', 'kode');

        $topLabels = $top->map(function ($r) use ($namaByKode) {
            if ($r->kode_kantor === null || $r->kode_kantor === '') {
                return 'Tanpa kantor';
            }

            return $r->kode_kantor.' — '.($namaByKode[$r->kode_kantor] ?? $r->kode_kantor);
        })->all();
        $topData = $top->pluck('total')->all();

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

    /** JSON modern untuk dashboard statistik IT. */
    public function reportDashboardData(Request $request)
    {
        Log::info('StatsController::reportDashboardData called', [
            'month' => $request->query('month'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
        ]);

        $month = $request->query('month', null);
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $periodStart = null;
        $periodEnd = null;
        $rangeLabel = 'Semua data';

        $formatDuration = static function ($seconds): array {
            if ($seconds === null) {
                return ['human' => '-', 'hours' => null];
            }

            $seconds = (float) $seconds;
            $hoursValue = $seconds / 3600.0;

            if ($seconds < 60) {
                $human = round($seconds).' detik';
            } elseif ($seconds < 3600) {
                $human = round($seconds / 60).' menit';
            } elseif ($seconds < 86400) {
                $hours = floor($seconds / 3600);
                $mins = floor(($seconds % 3600) / 60);
                $human = $hours.' jam'.($mins ? ' '.$mins.' mnt' : '');
            } else {
                $days = floor($seconds / 86400);
                $hours = floor(($seconds % 86400) / 3600);
                $human = $days.' hari'.($hours ? ' '.$hours.' jam' : '');
            }

            return ['human' => $human, 'hours' => round($hoursValue, 2)];
        };

        try {
            $tickets = Ticket::query();

            $this->applyKantorFilterToTickets($tickets, $request);

            if ($dateFrom || $dateTo) {
                if ($dateFrom) {
                    try {
                        $periodStart = Carbon::createFromFormat('Y-m-d', $dateFrom)->startOfDay();
                    } catch (\Exception $e) {
                        return response()->json(['message' => 'Format date_from tidak valid'], 422);
                    }
                    $tickets->where('tickets.created_at', '>=', $periodStart);
                }

                if ($dateTo) {
                    try {
                        $periodEnd = Carbon::createFromFormat('Y-m-d', $dateTo)->endOfDay();
                    } catch (\Exception $e) {
                        return response()->json(['message' => 'Format date_to tidak valid'], 422);
                    }
                    $tickets->where('tickets.created_at', '<=', $periodEnd);
                }

                if ($periodStart && $periodEnd) {
                    $rangeLabel = $periodStart->format('d/m/Y').' - '.$periodEnd->format('d/m/Y');
                } elseif ($periodStart) {
                    $rangeLabel = 'Mulai '.$periodStart->format('d/m/Y');
                } elseif ($periodEnd) {
                    $rangeLabel = 'Sampai '.$periodEnd->format('d/m/Y');
                }
            } elseif ($month && $month !== 'all') {
                try {
                    $periodStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
                    $periodEnd = (clone $periodStart)->endOfMonth();
                    $tickets->whereBetween('tickets.created_at', [$periodStart, $periodEnd]);
                    $rangeLabel = $periodStart->format('m/Y');
                } catch (\Exception $e) {
                    Log::warning('Invalid month format in stats request', ['month' => $month]);

                    return response()->json(['message' => 'Format bulan tidak valid'], 422);
                }
            }

            $selectedKodeKantor = trim((string) $request->input('kode_kantor', $request->query('kode_kantor', '')));
            $scopeLabel = 'Semua kantor';
            if ($selectedKodeKantor !== '') {
                $namaKantor = KodeKantor::where('kode', $selectedKodeKantor)->value('nama_kantor');
                $scopeLabel = $selectedKodeKantor.' - '.($namaKantor ?: $selectedKodeKantor);
            }

            $kpiTotal = (clone $tickets)->count();
            $kpiClosed = (clone $tickets)->where('status', 'CLOSED')->count();
            $kpiActive = max(0, $kpiTotal - $kpiClosed);
            $kpiOpenQueue = (clone $tickets)->where('status', 'OPEN')->count();
            $kpiInProgress = (clone $tickets)->where('status', 'ON_PROGRESS')->count();
            $kpiEscalated = (clone $tickets)->where('status', 'ESKALASI_VENDOR')->count();
            $kpiVendorResolved = (clone $tickets)->where('status', 'VENDOR_RESOLVED')->count();
            $kpiUnassigned = (clone $tickets)->where('status', 'OPEN')->whereNull('it_id')->count();
            $completionRate = $kpiTotal > 0 ? round(($kpiClosed / $kpiTotal) * 100, 1) : 0.0;

            $avgResolution = $formatDuration(
                (clone $tickets)
                    ->whereNotNull('closed_at')
                    ->select(DB::raw('AVG(TIMESTAMPDIFF(SECOND, tickets.created_at, tickets.closed_at)) as avg_seconds'))
                    ->value('avg_seconds')
            );

            $avgResponse = $formatDuration(
                (clone $tickets)
                    ->whereNotNull('taken_at')
                    ->select(DB::raw('AVG(TIMESTAMPDIFF(SECOND, tickets.created_at, tickets.taken_at)) as avg_response_seconds'))
                    ->value('avg_response_seconds')
            );

            $kategori = (clone $tickets)
                ->leftJoin('categories', 'tickets.category_id', '=', 'categories.id')
                ->select(DB::raw('COALESCE(categories.name, tickets.kategori, "Lainnya") as category_name'), DB::raw('count(tickets.id) as total'))
                ->groupBy('category_name')
                ->orderByDesc('total')
                ->limit(10)
                ->get();

            $kategoriRows = $kategori
                ->map(fn ($row) => [
                    'label' => (string) ($row->category_name ?: 'Lainnya'),
                    'total' => (int) $row->total,
                    'percent' => $kpiTotal > 0 ? round(((int) $row->total / $kpiTotal) * 100, 1) : 0,
                ])
                ->values();

            $statusRaw = (clone $tickets)
                ->select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->get()
                ->keyBy('status');

            $statusOrder = ['OPEN', 'ON_PROGRESS', 'ESKALASI_VENDOR', 'VENDOR_RESOLVED', 'CLOSED'];
            $statusRows = collect($statusOrder)
                ->map(function ($status) use ($statusRaw, $kpiTotal) {
                    $total = (int) optional($statusRaw->get($status))->total;

                    return [
                        'status' => $status,
                        'total' => $total,
                        'percent' => $kpiTotal > 0 ? round(($total / $kpiTotal) * 100, 1) : 0,
                    ];
                })
                ->concat(
                    $statusRaw
                        ->keys()
                        ->diff($statusOrder)
                        ->map(function ($status) use ($statusRaw, $kpiTotal) {
                            $total = (int) optional($statusRaw->get($status))->total;

                            return [
                                'status' => $status,
                                'total' => $total,
                                'percent' => $kpiTotal > 0 ? round(($total / $kpiTotal) * 100, 1) : 0,
                            ];
                        })
                )
                ->filter(fn ($row) => $row['total'] > 0)
                ->values();

            $top = (clone $tickets)
                ->join('users', 'tickets.user_id', '=', 'users.id')
                ->select('users.kode_kantor', DB::raw('count(tickets.id) as total'))
                ->groupBy('users.kode_kantor')
                ->orderByDesc('total')
                ->limit(8)
                ->get();

            $kodeList = $top->pluck('kode_kantor')->filter()->values();
            $namaByKode = KodeKantor::whereIn('kode', $kodeList)->pluck('nama_kantor', 'kode');
            $topRows = $top
                ->map(function ($row) use ($namaByKode, $kpiTotal) {
                    $kode = (string) ($row->kode_kantor ?? '');
                    $label = $kode === '' ? 'Tanpa kantor' : $kode.' - '.($namaByKode[$kode] ?? $kode);

                    return [
                        'kode' => $kode !== '' ? $kode : null,
                        'label' => $label,
                        'total' => (int) $row->total,
                        'percent' => $kpiTotal > 0 ? round(((int) $row->total / $kpiTotal) * 100, 1) : 0,
                    ];
                })
                ->values();

            $root = (clone $tickets)
                ->select('root_cause', DB::raw('count(*) as total'))
                ->where('status', 'CLOSED')
                ->whereNotNull('root_cause')
                ->where('root_cause', '!=', '')
                ->groupBy('root_cause')
                ->orderByDesc('total')
                ->limit(10)
                ->get();

            $rootRows = $root
                ->map(fn ($row) => [
                    'label' => (string) ($row->root_cause ?: 'Tidak ditentukan'),
                    'total' => (int) $row->total,
                    'percent' => $kpiClosed > 0 ? round(((int) $row->total / $kpiClosed) * 100, 1) : 0,
                ])
                ->values();

            $trendEnd = $periodEnd ? (clone $periodEnd) : now()->endOfDay();
            $trendStart = $periodStart ? (clone $periodStart) : (clone $trendEnd)->subDays(29)->startOfDay();
            if ($trendStart->greaterThan($trendEnd)) {
                $tmp = $trendStart;
                $trendStart = (clone $trendEnd)->startOfDay();
                $trendEnd = (clone $tmp)->endOfDay();
            }

            $trendGranularity = $trendStart->diffInDays($trendEnd) > 62 ? 'month' : 'day';
            if ($trendGranularity === 'month') {
                $trendStart = (clone $trendStart)->startOfMonth();
                $trendEnd = (clone $trendEnd)->endOfMonth();
            }

            $trendExpr = $trendGranularity === 'month'
                ? 'DATE_FORMAT(tickets.created_at, "%Y-%m")'
                : 'DATE(tickets.created_at)';

            $trendQuery = Ticket::query()
                ->select(
                    DB::raw($trendExpr.' as period_key'),
                    DB::raw('count(tickets.id) as total'),
                    DB::raw("SUM(CASE WHEN tickets.status = 'CLOSED' THEN 1 ELSE 0 END) as closed_total")
                )
                ->whereBetween('tickets.created_at', [$trendStart, $trendEnd])
                ->groupBy('period_key')
                ->orderBy('period_key');
            $this->applyKantorFilterToTickets($trendQuery, $request);
            $trendRows = $trendQuery->get()->keyBy('period_key');

            $trendLabels = [];
            $trendKeys = [];
            $cursor = (clone $trendStart);
            while ($cursor <= $trendEnd) {
                if ($trendGranularity === 'month') {
                    $trendKeys[] = $cursor->format('Y-m');
                    $trendLabels[] = $cursor->format('M y');
                    $cursor->addMonth();
                } else {
                    $trendKeys[] = $cursor->toDateString();
                    $trendLabels[] = $cursor->format('d/m');
                    $cursor->addDay();
                }
            }

            $trendCreated = [];
            $trendClosed = [];
            foreach ($trendKeys as $key) {
                $row = $trendRows->get($key);
                $trendCreated[] = (int) optional($row)->total;
                $trendClosed[] = (int) optional($row)->closed_total;
            }

            $buildTrendDatasets = static function ($rows) use ($trendKeys) {
                $topLabels = $rows
                    ->groupBy('series_label')
                    ->map(fn ($group) => (int) $group->sum('total'))
                    ->sortDesc()
                    ->take(5);

                return $topLabels
                    ->map(function ($total, $label) use ($rows, $trendKeys) {
                        $periodMap = $rows
                            ->where('series_label', $label)
                            ->pluck('total', 'period_key');

                        return [
                            'label' => (string) ($label ?: 'Tidak ditentukan'),
                            'total' => (int) $total,
                            'data' => collect($trendKeys)
                                ->map(fn ($key) => (int) ($periodMap[$key] ?? 0))
                                ->values()
                                ->all(),
                        ];
                    })
                    ->values();
            };

            $categoryTrendQuery = Ticket::query()
                ->leftJoin('categories', 'tickets.category_id', '=', 'categories.id')
                ->select(
                    DB::raw('COALESCE(categories.name, tickets.kategori, "Lainnya") as series_label'),
                    DB::raw($trendExpr.' as period_key'),
                    DB::raw('count(tickets.id) as total')
                )
                ->whereBetween('tickets.created_at', [$trendStart, $trendEnd])
                ->groupBy('series_label', 'period_key')
                ->orderBy('period_key');
            $this->applyKantorFilterToTickets($categoryTrendQuery, $request);
            $categoryTrendDatasets = $buildTrendDatasets($categoryTrendQuery->get());

            $subcategoryTrendQuery = Ticket::query()
                ->leftJoin('subcategories', 'tickets.subcategory_id', '=', 'subcategories.id')
                ->select(
                    DB::raw('COALESCE(subcategories.name, "Tidak ditentukan") as series_label'),
                    DB::raw($trendExpr.' as period_key'),
                    DB::raw('count(tickets.id) as total')
                )
                ->whereBetween('tickets.created_at', [$trendStart, $trendEnd])
                ->groupBy('series_label', 'period_key')
                ->orderBy('period_key');
            $this->applyKantorFilterToTickets($subcategoryTrendQuery, $request);
            $subcategoryTrendDatasets = $buildTrendDatasets($subcategoryTrendQuery->get());

            $rootCauseTrendQuery = Ticket::query()
                ->select(
                    DB::raw('COALESCE(NULLIF(tickets.root_cause, ""), "Tidak ditentukan") as series_label'),
                    DB::raw($trendExpr.' as period_key'),
                    DB::raw('count(tickets.id) as total')
                )
                ->whereBetween('tickets.created_at', [$trendStart, $trendEnd])
                ->where('tickets.status', 'CLOSED')
                ->whereNotNull('tickets.root_cause')
                ->where('tickets.root_cause', '!=', '')
                ->groupBy('series_label', 'period_key')
                ->orderBy('period_key');
            $this->applyKantorFilterToTickets($rootCauseTrendQuery, $request);
            $rootCauseTrendDatasets = $buildTrendDatasets($rootCauseTrendQuery->get());

            $officeTrendQuery = Ticket::query()
                ->join('users', 'tickets.user_id', '=', 'users.id')
                ->leftJoin('kode_kantor', 'users.kode_kantor', '=', 'kode_kantor.kode')
                ->select(
                    DB::raw('CASE WHEN IFNULL(users.kode_kantor, "") = "" THEN "Tanpa kantor" ELSE CONCAT(users.kode_kantor, " - ", COALESCE(kode_kantor.nama_kantor, users.kode_kantor)) END as series_label'),
                    DB::raw($trendExpr.' as period_key'),
                    DB::raw('count(tickets.id) as total')
                )
                ->whereBetween('tickets.created_at', [$trendStart, $trendEnd])
                ->groupBy('series_label', 'period_key')
                ->orderBy('period_key');
            $this->applyKantorFilterToTickets($officeTrendQuery, $request);
            $officeTrendDatasets = $buildTrendDatasets($officeTrendQuery->get());

            $now = now();
            $oneDayAgo = (clone $now)->subDay();
            $threeDaysAgo = (clone $now)->subDays(3);
            $sevenDaysAgo = (clone $now)->subDays(7);
            $activeTickets = (clone $tickets)->where('status', '!=', 'CLOSED');

            $agingRows = collect([
                ['label' => '0-1 hari', 'total' => (clone $activeTickets)->where('tickets.created_at', '>=', $oneDayAgo)->count()],
                ['label' => '1-3 hari', 'total' => (clone $activeTickets)->where('tickets.created_at', '<', $oneDayAgo)->where('tickets.created_at', '>=', $threeDaysAgo)->count()],
                ['label' => '3-7 hari', 'total' => (clone $activeTickets)->where('tickets.created_at', '<', $threeDaysAgo)->where('tickets.created_at', '>=', $sevenDaysAgo)->count()],
                ['label' => '> 7 hari', 'total' => (clone $activeTickets)->where('tickets.created_at', '<', $sevenDaysAgo)->count()],
            ])
                ->map(fn ($row) => [
                    'label' => $row['label'],
                    'total' => (int) $row['total'],
                    'percent' => $kpiActive > 0 ? round(((int) $row['total'] / $kpiActive) * 100, 1) : 0,
                ])
                ->values();
            $agingCritical = $agingRows->firstWhere('label', '> 7 hari');
            $agingCriticalTotal = is_array($agingCritical) ? (int) $agingCritical['total'] : 0;

            return response()->json([
                'meta' => [
                    'range_label' => $rangeLabel,
                    'scope_label' => $scopeLabel,
                    'generated_at' => now()->format('d/m/Y H:i'),
                    'trend_label' => $trendGranularity === 'month'
                        ? $trendStart->format('M Y').' - '.$trendEnd->format('M Y')
                        : $trendStart->format('d/m/Y').' - '.$trendEnd->format('d/m/Y'),
                    'trend_granularity' => $trendGranularity,
                ],
                'kpi' => [
                    'total' => $kpiTotal,
                    'open' => $kpiActive,
                    'active' => $kpiActive,
                    'open_queue' => $kpiOpenQueue,
                    'in_progress' => $kpiInProgress,
                    'escalated' => $kpiEscalated,
                    'vendor_resolved' => $kpiVendorResolved,
                    'closed' => $kpiClosed,
                    'unassigned' => $kpiUnassigned,
                    'completion_rate' => $completionRate,
                    'avg_resolution' => $avgResolution['human'],
                    'avg_resolution_hours' => $avgResolution['hours'],
                    'avg_response' => $avgResponse['human'],
                    'avg_response_hours' => $avgResponse['hours'],
                    'aging_over_7_days' => $agingCriticalTotal,
                ],
                'trend' => [
                    'labels' => $trendLabels,
                    'created' => $trendCreated,
                    'closed' => $trendClosed,
                    'breakdowns' => [
                        'kategori' => [
                            'label' => 'Kategori',
                            'datasets' => $categoryTrendDatasets,
                        ],
                        'subkategori' => [
                            'label' => 'Sub kategori',
                            'datasets' => $subcategoryTrendDatasets,
                        ],
                        'root_cause' => [
                            'label' => 'Root cause',
                            'datasets' => $rootCauseTrendDatasets,
                        ],
                        'kantor' => [
                            'label' => 'Cabang',
                            'datasets' => $officeTrendDatasets,
                        ],
                    ],
                ],
                'statusRows' => $statusRows,
                'kategoriRows' => $kategoriRows,
                'topRows' => $topRows,
                'rootRows' => $rootRows,
                'agingRows' => $agingRows,
                'kategoriLabels' => $kategoriRows->pluck('label')->all(),
                'kategoriData' => $kategoriRows->pluck('total')->all(),
                'statusLabels' => $statusRows->pluck('status')->all(),
                'statusData' => $statusRows->pluck('total')->all(),
                'topLabels' => $topRows->pluck('label')->all(),
                'topData' => $topRows->pluck('total')->all(),
                'rootLabels' => $rootRows->pluck('label')->all(),
                'rootData' => $rootRows->pluck('total')->all(),
            ]);
        } catch (\Throwable $e) {
            Log::error('StatsController::reportDashboardData error: '.$e->getMessage(), [
                'exception' => $e,
                'month' => $month,
            ]);

            if (app()->environment('local') || app()->environment('development')) {
                return response()->json(['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
            }

            return response()->json(['message' => 'Server error'], 500);
        }
    }

    /** JSON: ringkasan eksekutif AI untuk pratinjau sebelum generate PDF */
    public function reportSummaryPreview(Request $request)
    {
        if (auth()->user()->role !== 'IT') {
            abort(403);
        }

        $report = $this->buildReportViewData($request);
        $executiveSummary = ExecutiveSummaryService::generate($report['summaryPayload']);

        return response()->json([
            'executive_summary' => $executiveSummary ?? '',
            'ai_unavailable' => empty($executiveSummary),
        ]);
    }

    /** Generate PDF report for given date range (POST dengan executive_summary = teks final dari modal) */
    public function report(Request $request)
    {
        if (auth()->user()->role !== 'IT') {
            abort(403);
        }

        $report = $this->buildReportViewData($request);

        if ($request->isMethod('post')) {
            $executiveSummary = (string) $request->input('executive_summary', '');
        } else {
            $executiveSummary = ExecutiveSummaryService::generate($report['summaryPayload']);
            if (empty($executiveSummary)) {
                $executiveSummary = 'Ringkasan AI belum tersedia untuk laporan ini. Silakan cek konfigurasi OPENAI_API_KEY atau coba generate ulang.';
            }
        }

        $dateFrom = $report['dateFrom'];
        $dateTo = $report['dateTo'];
        $total = $report['total'];
        $closed = $report['closed'];
        $open = $report['open'];
        $eskCount = $report['eskCount'];
        $onProgress = $report['onProgress'];
        $root = $report['root'];
        $ticketsList = $report['ticketsList'];
        $groupedTickets = $report['groupedTickets'];
        $kpiChartUrl = $report['kpiChartUrl'];
        $categoryChartUrl = $report['categoryChartUrl'];
        $subcategoryTrendChartUrl = $report['subcategoryTrendChartUrl'];
        $rootCauseTrendChartUrl = $report['rootCauseTrendChartUrl'];
        $reporterTrendChartUrl = $report['reporterTrendChartUrl'];

        $html = view('it.stats_pdf', [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'kpi' => ['total' => $total, 'open' => $open, 'closed' => $closed, 'eskalasi' => $eskCount, 'on_progress' => $onProgress],
            'root' => $root,
            'tickets' => $ticketsList,
            'groupedTickets' => $groupedTickets,
            'kpiChartUrl' => $kpiChartUrl,
            'categoryChartUrl' => $categoryChartUrl,
            'subcategoryTrendChartUrl' => $subcategoryTrendChartUrl,
            'rootCauseTrendChartUrl' => $rootCauseTrendChartUrl,
            'reporterTrendChartUrl' => $reporterTrendChartUrl,
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

    /**
     * @return array{
     *   dateFrom: mixed,
     *   dateTo: mixed,
     *   total: int,
     *   closed: int,
     *   open: int,
     *   eskCount: int,
     *   onProgress: int,
     *   root: \Illuminate\Support\Collection,
     *   ticketsList: \Illuminate\Support\Collection,
     *   groupedTickets: \Illuminate\Support\Collection,
     *   summaryPayload: array,
     *   kpiChartUrl: string,
     *   categoryChartUrl: string,
     *   subcategoryTrendChartUrl: string,
     *   rootCauseTrendChartUrl: string,
     *   reporterTrendChartUrl: string
     * }
     */
    private function buildReportViewData(Request $request): array
    {
        $dateFrom = $request->input('date_from', $request->query('date_from'));
        $dateTo = $request->input('date_to', $request->query('date_to'));

        $ticketsBase = \App\Models\Ticket::query();
        if ($dateFrom) {
            try {
                $df = Carbon::createFromFormat('Y-m-d', $dateFrom)->startOfDay();
                $ticketsBase->where('tickets.created_at', '>=', $df);
            } catch (\Exception $e) {
                /* ignore */
            }
        }
        if ($dateTo) {
            try {
                $dt = Carbon::createFromFormat('Y-m-d', $dateTo)->endOfDay();
                $ticketsBase->where('tickets.created_at', '<=', $dt);
            } catch (\Exception $e) {
                /* ignore */
            }
        }

        $this->applyKantorFilterToTickets($ticketsBase, $request);

        $total = (clone $ticketsBase)->count();
        $closed = (clone $ticketsBase)->where('status', 'CLOSED')->count();
        $open = max(0, $total - $closed);

        $eskCount = (clone $ticketsBase)
            ->where(function ($q) {
                $q->where('status', 'ESKALASI_VENDOR')
                    ->orWhereHas('histories', fn ($h) => $h->where('action', 'assigned_vendor'));
            })->count();

        $onProgress = (clone $ticketsBase)->where('status', 'ON_PROGRESS')->count();

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

        $ticketsList = (clone $ticketsBase)
            ->with(['user.kodeKantor', 'subcategory', 'rootCauseDetail'])
            ->orderByDesc('created_at')
            ->get([
                'id', 'nomor_tiket', 'created_at', 'user_id', 'kategori', 'subcategory_id',
                'root_cause', 'root_cause_detail_id', 'status', 'closed_at', 'closed_note',
                'taken_at', 'progress_note', 'vendor_followup',
            ]);

        $groupedTickets = $ticketsList
            ->groupBy(function ($t) {
                $kode = $t->user?->kode_kantor;
                if ($kode) {
                    return $kode.' — '.($t->user?->kodeKantor?->nama_kantor ?? $kode);
                }

                return 'Tanpa kantor';
            })
            ->sortKeys();

        $textForAi = static function (?string $value, int $max = 4000): string {
            $v = trim((string) ($value ?? ''));

            return $v === '' ? 'tidak tersedia' : Str::limit($v, $max, '…');
        };

        $ticketPayload = $ticketsList->map(function ($t) use ($textForAi) {
            $responseMinutes = null;
            if (! empty($t->taken_at) && ! empty($t->created_at)) {
                $responseMinutes = $t->created_at->diffInMinutes($t->taken_at);
            }

            return [
                'pembuat_tiket' => optional($t->user)->name ?? 'tidak tersedia',
                'kode_kantor_pembuat' => $t->user?->kode_kantor ?? 'tidak tersedia',
                'nama_kantor_pembuat' => $t->user?->kodeKantor?->nama_kantor ?? 'tidak tersedia',
                'kategori_tiket' => $t->kategori ?? 'tidak tersedia',
                'sub_kategori_tiket' => optional($t->subcategory)->name ?? 'tidak tersedia',
                'root_cause' => $t->root_cause ?? 'tidak tersedia',
                'detail_root_cause' => optional($t->rootCauseDetail)->label ?? 'tidak tersedia',
                'closed_note' => $textForAi($t->closed_note),
                'tindak_lanjut_progres_it' => $textForAi($t->progress_note),
                'tindak_lanjut_vendor' => $textForAi($t->vendor_followup),
                'response_time_ti_menit' => $responseMinutes ?? 'tidak tersedia',
            ];
        })->values()->all();

        $detailRootStats = (clone $ticketsBase)
            ->where('tickets.status', 'CLOSED')
            ->whereNotNull('tickets.root_cause_detail_id')
            ->join('root_cause_details', 'tickets.root_cause_detail_id', '=', 'root_cause_details.id')
            ->select('root_cause_details.label', DB::raw('count(tickets.id) as total'))
            ->groupBy('root_cause_details.label')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'label' => $row->label,
                'jumlah' => (int) $row->total,
            ])
            ->values()
            ->all();

        $closedTanpaDetailRoot = (clone $ticketsBase)
            ->where('status', 'CLOSED')
            ->whereNull('root_cause_detail_id')
            ->count();

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
            'statistik_detail_root_cause' => $detailRootStats,
            'jumlah_tiket_tutup_tanpa_detail_root_cause' => $closedTanpaDetailRoot,
            'data_tiket' => $ticketPayload,
        ];

        $kpiLabels = ['Open', 'Closed', 'Eskalasi Vendor', 'On Progress'];
        $kpiValues = [$open, $closed, $eskCount, $onProgress];
        $kpiColors = ['#ef4444', '#10b981', '#8b5cf6', '#f59e0b'];
        $chartConfig = [
            'type' => 'doughnut',
            'data' => [
                'labels' => $kpiLabels,
                'datasets' => [[
                    'data' => $kpiValues,
                    'backgroundColor' => $kpiColors,
                ]],
            ],
            'options' => [
                'plugins' => [
                    'legend' => ['display' => true, 'position' => 'bottom'],
                ],
            ],
        ];
        $kpiChartUrl = 'https://quickchart.io/chart?c=' . urlencode(json_encode($chartConfig));

        // Rentang tren 12 bulan dipakai oleh chart kategori & sub kategori
        $trendEnd = $dateTo
            ? Carbon::createFromFormat('Y-m-d', $dateTo)->endOfMonth()
            : now()->endOfMonth();
        $trendStart = (clone $trendEnd)->subMonths(11)->startOfMonth();

        $monthLabels = [];
        $monthKeys = [];
        $cursor = (clone $trendStart);
        while ($cursor <= $trendEnd) {
            $monthLabels[] = $cursor->translatedFormat('M y');
            $monthKeys[] = $cursor->format('Y-m');
            $cursor->addMonth();
        }

        $catTrendQuery = \App\Models\Ticket::query()
            ->select(
                DB::raw('COALESCE(tickets.kategori, "Tidak Ditentukan") as kategori'),
                DB::raw('DATE_FORMAT(tickets.created_at, "%Y-%m") as ym'),
                DB::raw('count(tickets.id) as total')
            )
            ->whereBetween('tickets.created_at', [$trendStart, $trendEnd])
            ->groupBy('kategori', 'ym')
            ->orderBy('ym');

        $this->applyKantorFilterToTickets($catTrendQuery, $request);

        $catTrendRows = $catTrendQuery->get();
        $topCategories = $catTrendRows
            ->groupBy('kategori')
            ->map(fn ($rows) => (int) $rows->sum('total'))
            ->sortDesc()
            ->take(5)
            ->keys()
            ->values();

        $palette = ['#3b82f6', '#22c55e', '#f59e0b', '#a855f7', '#ef4444'];
        $catTrendDatasets = [];
        foreach ($topCategories as $i => $kategori) {
            $monthlyMap = $catTrendRows
                ->where('kategori', $kategori)
                ->pluck('total', 'ym');
            $catTrendDatasets[] = [
                'label' => $kategori,
                'data' => collect($monthKeys)->map(fn ($ym) => (int) ($monthlyMap[$ym] ?? 0))->values()->all(),
                'borderColor' => $palette[$i % count($palette)],
                'backgroundColor' => $palette[$i % count($palette)] . '33',
                'fill' => true,
                'pointRadius' => 2,
                'pointHoverRadius' => 4,
                'borderWidth' => 2,
                'tension' => 0.35,
            ];
        }

        $categoryChartConfig = [
            'type' => 'line',
            'data' => [
                'labels' => $monthLabels,
                'datasets' => $catTrendDatasets,
            ],
            'options' => [
                'elements' => [
                    'line' => ['capBezierPoints' => true],
                ],
                'plugins' => [
                    'legend' => ['display' => true, 'position' => 'bottom'],
                ],
                'scales' => [
                    'y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]],
                    'x' => [
                        'ticks' => [
                            'autoSkip' => false,
                            'maxRotation' => 0,
                            'minRotation' => 0,
                            'font' => ['size' => 10],
                        ],
                    ],
                ],
                'layout' => [
                    'padding' => ['top' => 6, 'left' => 6, 'right' => 10, 'bottom' => 0],
                ],
            ],
        ];
        $categoryChartUrl = 'https://quickchart.io/chart?c=' . urlencode(json_encode($categoryChartConfig));

        $subcatTrendQuery = \App\Models\Ticket::query()
            ->leftJoin('subcategories', 'tickets.subcategory_id', '=', 'subcategories.id')
            ->select(
                DB::raw('COALESCE(subcategories.name, "Tidak Ditentukan") as sub_kategori'),
                DB::raw('DATE_FORMAT(tickets.created_at, "%Y-%m") as ym'),
                DB::raw('count(tickets.id) as total')
            )
            ->whereBetween('tickets.created_at', [$trendStart, $trendEnd])
            ->groupBy('sub_kategori', 'ym')
            ->orderBy('ym');

        $this->applyKantorFilterToTickets($subcatTrendQuery, $request);

        $subcatTrendRows = $subcatTrendQuery->get();
        $topSubcats = $subcatTrendRows
            ->groupBy('sub_kategori')
            ->map(fn ($rows) => (int) $rows->sum('total'))
            ->sortDesc()
            ->take(5)
            ->keys()
            ->values();

        $trendDatasets = [];
        foreach ($topSubcats as $i => $subcat) {
            $monthlyMap = $subcatTrendRows
                ->where('sub_kategori', $subcat)
                ->pluck('total', 'ym');
            $trendDatasets[] = [
                'label' => $subcat,
                'data' => collect($monthKeys)->map(fn ($ym) => (int) ($monthlyMap[$ym] ?? 0))->values()->all(),
                'borderColor' => $palette[$i % count($palette)],
                'backgroundColor' => $palette[$i % count($palette)] . '33',
                'fill' => true,
                'pointRadius' => 2,
                'pointHoverRadius' => 4,
                'borderWidth' => 2,
                'tension' => 0.35,
            ];
        }

        $subcatTrendConfig = [
            'type' => 'line',
            'data' => [
                'labels' => $monthLabels,
                'datasets' => $trendDatasets,
            ],
            'options' => [
                'elements' => [
                    'line' => ['capBezierPoints' => true],
                ],
                'plugins' => [
                    'legend' => ['display' => true, 'position' => 'bottom'],
                ],
                'scales' => [
                    'y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]],
                    'x' => [
                        'ticks' => [
                            'autoSkip' => false,
                            'maxRotation' => 0,
                            'minRotation' => 0,
                            'font' => ['size' => 10],
                        ],
                    ],
                ],
                'layout' => [
                    'padding' => ['top' => 6, 'left' => 6, 'right' => 10, 'bottom' => 0],
                ],
            ],
        ];
        $subcategoryTrendChartUrl = 'https://quickchart.io/chart?c=' . urlencode(json_encode($subcatTrendConfig));

        $rootTrendQuery = \App\Models\Ticket::query()
            ->select(
                DB::raw('COALESCE(tickets.root_cause, "Tidak Ditentukan") as root_cause'),
                DB::raw('DATE_FORMAT(tickets.created_at, "%Y-%m") as ym'),
                DB::raw('count(tickets.id) as total')
            )
            ->whereBetween('tickets.created_at', [$trendStart, $trendEnd])
            ->groupBy('root_cause', 'ym')
            ->orderBy('ym');

        $this->applyKantorFilterToTickets($rootTrendQuery, $request);

        $rootTrendRows = $rootTrendQuery->get();
        $topRootCauses = $rootTrendRows
            ->groupBy('root_cause')
            ->map(fn ($rows) => (int) $rows->sum('total'))
            ->sortDesc()
            ->take(5)
            ->keys()
            ->values();

        $rootTrendDatasets = [];
        foreach ($topRootCauses as $i => $rootCause) {
            $monthlyMap = $rootTrendRows
                ->where('root_cause', $rootCause)
                ->pluck('total', 'ym');
            $rootTrendDatasets[] = [
                'label' => $rootCause,
                'data' => collect($monthKeys)->map(fn ($ym) => (int) ($monthlyMap[$ym] ?? 0))->values()->all(),
                'borderColor' => $palette[$i % count($palette)],
                'backgroundColor' => $palette[$i % count($palette)] . '33',
                'fill' => true,
                'pointRadius' => 2,
                'pointHoverRadius' => 4,
                'borderWidth' => 2,
                'tension' => 0.35,
            ];
        }

        $rootTrendConfig = [
            'type' => 'line',
            'data' => [
                'labels' => $monthLabels,
                'datasets' => $rootTrendDatasets,
            ],
            'options' => [
                'plugins' => [
                    'legend' => ['display' => true, 'position' => 'bottom'],
                ],
                'scales' => [
                    'y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]],
                    'x' => [
                        'ticks' => [
                            'autoSkip' => false,
                            'maxRotation' => 0,
                            'minRotation' => 0,
                            'font' => ['size' => 10],
                        ],
                    ],
                ],
            ],
        ];
        $rootCauseTrendChartUrl = 'https://quickchart.io/chart?c=' . urlencode(json_encode($rootTrendConfig));

        $reporterTrendQuery = \App\Models\Ticket::query()
            ->join('users', 'tickets.user_id', '=', 'users.id')
            ->leftJoin('kode_kantor', 'users.kode_kantor', '=', 'kode_kantor.kode')
            ->select(
                DB::raw('CASE WHEN IFNULL(MAX(users.kode_kantor), "") = "" THEN "Tanpa kantor" ELSE CONCAT(MAX(users.kode_kantor), " — ", COALESCE(MAX(kode_kantor.nama_kantor), MAX(users.kode_kantor))) END as reporter'),
                DB::raw('DATE_FORMAT(tickets.created_at, "%Y-%m") as ym'),
                DB::raw('count(tickets.id) as total')
            )
            ->whereBetween('tickets.created_at', [$trendStart, $trendEnd])
            ->groupBy(DB::raw('IFNULL(users.kode_kantor, "")'), DB::raw('DATE_FORMAT(tickets.created_at, "%Y-%m")'))
            ->orderBy('ym');

        $this->applyKantorFilterToTickets($reporterTrendQuery, $request);

        $reporterTrendRows = $reporterTrendQuery->get();
        $topReporters = $reporterTrendRows
            ->groupBy('reporter')
            ->map(fn ($rows) => (int) $rows->sum('total'))
            ->sortDesc()
            ->take(5)
            ->keys()
            ->values();

        $reporterTrendDatasets = [];
        foreach ($topReporters as $i => $reporter) {
            $monthlyMap = $reporterTrendRows
                ->where('reporter', $reporter)
                ->pluck('total', 'ym');
            $reporterTrendDatasets[] = [
                'label' => $reporter,
                'data' => collect($monthKeys)->map(fn ($ym) => (int) ($monthlyMap[$ym] ?? 0))->values()->all(),
                'borderColor' => $palette[$i % count($palette)],
                'backgroundColor' => $palette[$i % count($palette)] . '33',
                'fill' => true,
                'pointRadius' => 2,
                'pointHoverRadius' => 4,
                'borderWidth' => 2,
                'tension' => 0.35,
            ];
        }

        $reporterTrendConfig = [
            'type' => 'line',
            'data' => [
                'labels' => $monthLabels,
                'datasets' => $reporterTrendDatasets,
            ],
            'options' => [
                'plugins' => [
                    'legend' => ['display' => true, 'position' => 'bottom'],
                ],
                'scales' => [
                    'y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]],
                    'x' => [
                        'ticks' => [
                            'autoSkip' => false,
                            'maxRotation' => 0,
                            'minRotation' => 0,
                            'font' => ['size' => 10],
                        ],
                    ],
                ],
            ],
        ];
        $reporterTrendChartUrl = 'https://quickchart.io/chart?c=' . urlencode(json_encode($reporterTrendConfig));

        return [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'total' => $total,
            'closed' => $closed,
            'open' => $open,
            'eskCount' => $eskCount,
            'onProgress' => $onProgress,
            'root' => $root,
            'ticketsList' => $ticketsList,
            'groupedTickets' => $groupedTickets,
            'summaryPayload' => $summaryPayload,
            'kpiChartUrl' => $kpiChartUrl,
            'categoryChartUrl' => $categoryChartUrl,
            'subcategoryTrendChartUrl' => $subcategoryTrendChartUrl,
            'rootCauseTrendChartUrl' => $rootCauseTrendChartUrl,
            'reporterTrendChartUrl' => $reporterTrendChartUrl,
        ];
    }
}
