<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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

    $month = $request->query('month', 'all');

    try {
        // base query (tickets)
        $tickets = \App\Models\Ticket::query();

        // filter by month (kualifikasi kolom supaya tidak ambiguous saat join)
        if ($month !== 'all') {
            try {
                $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
                $end   = (clone $start)->endOfMonth();
            } catch (\Exception $e) {
                Log::warning('Invalid month format in stats request', ['month' => $month]);
                return response()->json(['message' => 'Format bulan tidak valid'], 422);
            }
            $tickets->whereBetween('tickets.created_at', [$start, $end]);
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
}
