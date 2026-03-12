@extends('layouts.app')
@section('title','Statistik Tiket')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
  {{-- Header + Filter (sticky di mobile tidak berlebihan) --}}
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
    <div>
      <h1 class="text-2xl font-semibold text-gray-800">Statistik Tiket</h1>
      <p class="text-sm text-gray-500 mt-1">Ringkasan permasalahan & root cause — filter per bulan.</p>
    </div>

    <div class="w-full sm:w-auto">
      <form id="filterForm" class="flex flex-col sm:flex-row sm:items-center gap-2">
        <label for="filterFrom" class="sr-only">Dari</label>
        <input id="filterFrom" name="date_from" type="date" class="w-full sm:w-40 rounded-md border border-gray-200 px-3 py-2 bg-white text-sm" />
        <label for="filterTo" class="sr-only">Sampai</label>
        <input id="filterTo" name="date_to" type="date" class="w-full sm:w-40 rounded-md border border-gray-200 px-3 py-2 bg-white text-sm" />

        <div class="flex gap-2">
          <button id="btnRefresh" type="button" class="inline-flex items-center justify-center px-3 py-2 rounded-md bg-sky-600 text-white text-sm shadow-sm hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500" aria-label="Refresh statistik">
            <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 12a9 9 0 1 1-3-6.5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Refresh
          </button>

          <button id="btnDownload" type="button" class="inline-flex items-center justify-center px-3 py-2 rounded-md border border-gray-200 bg-white text-sm text-gray-700 hover:bg-gray-50" aria-label="Download laporan">
            <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 3v12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 11l4 4 4-4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M21 21H3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Export
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- Loading overlay (hidden by default) --}}
  <div id="loadingOverlay" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 hidden">
    <div class="bg-white rounded-lg p-4 flex items-center gap-3 shadow-lg">
      <svg class="animate-spin w-6 h-6 text-sky-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
      <div>
        <div class="text-sm font-medium text-gray-800">Memuat statistik…</div>
        <div class="text-xs text-gray-500">Tunggu sebentar — ini normal jika data banyak.</div>
      </div>
    </div>
  </div>

  {{-- Grid utama: responsive: 1 col mobile, 2 col tablet, 3 col desktop --}}
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    {{-- KPI Card: Total, Open, Closed, Avg Res --}}
    <div class="lg:col-span-1 col-span-1">
      <div class="bg-white p-4 rounded-2xl shadow-sm ring-1 ring-gray-100 space-y-3 h-full">
        <h3 class="text-sm font-semibold text-gray-700">Ringkasan</h3>

        <div id="kpiGrid" class="grid grid-cols-2 gap-3">
          <div class="p-3 bg-gray-50 rounded-lg">
            <div class="text-xs text-gray-500">Total Tiket</div>
            <div id="kpiTotal" class="text-lg font-semibold text-gray-800 mt-1">—</div>
          </div>
          <div class="p-3 bg-gray-50 rounded-lg">
            <div class="text-xs text-gray-500">Tiket Terbuka</div>
            <div id="kpiOpen" class="text-lg font-semibold text-gray-800 mt-1">—</div>
          </div>
          <div class="p-3 bg-gray-50 rounded-lg">
            <div class="text-xs text-gray-500">Tiket Selesai</div>
            <div id="kpiClosed" class="text-lg font-semibold text-gray-800 mt-1">—</div>
          </div>
          <div class="p-3 bg-gray-50 rounded-lg">
            <div class="text-xs text-gray-500">Rata-rata Penyelesaian</div>
            <div id="kpiAvg" class="text-lg font-semibold text-gray-800 mt-1">—</div>
          </div>
          <div class="p-3 bg-gray-50 rounded-lg">
            <div class="text-xs text-gray-500">Respon Time TI</div>
            <div id="kpiResponse" class="text-lg font-semibold text-gray-800 mt-1">—</div>
          </div>
        </div>

        <div class="mt-3 text-xs text-gray-500">Tip: ubah periode untuk melihat KPI per bulan.</div>
      </div>
    </div>

    {{-- Kategori Chart (besar) --}}
    <div class="lg:col-span-2 md:col-span-1 col-span-1">
      <div class="bg-white p-4 rounded-2xl shadow-sm ring-1 ring-gray-100 h-full flex flex-col">
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-sm font-semibold text-gray-700">Kategori Tiket Terbanyak</h3>
          <span class="text-xs text-gray-500">Top 10</span>
        </div>
        <div class="flex-1 min-h-[180px]">
          <div class="h-full">
            <canvas id="chartKategori" class="w-full h-full"></canvas>
          </div>
        </div>
      </div>
    </div>

    {{-- Status Pie --}}
    <div class="col-span-1">
      <div class="bg-white p-4 rounded-2xl shadow-sm ring-1 ring-gray-100 h-full">
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-sm font-semibold text-gray-700">Status Tiket</h3>
          <span class="text-xs text-gray-500">Distribusi</span>
        </div>
        <div class="h-40 flex items-center justify-center">
          <canvas id="chartStatus" class="w-full h-full max-h-40"></canvas>
        </div>
      </div>
    </div>

    {{-- Top Users --}}
    <div class="lg:col-span-3 col-span-1">
      <div class="bg-white p-4 rounded-2xl shadow-sm ring-1 ring-gray-100">
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-sm font-semibold text-gray-700">Top 5 User Pembuat Tiket</h3>
          <div class="text-xs text-gray-500">Periode</div>
        </div>
        <div class="min-h-[160px]">
          <canvas id="chartTopUser" class="w-full h-40"></canvas>
        </div>
      </div>
    </div>

    {{-- Root Cause --}}
    <div class="lg:col-span-3 col-span-1">
      <div class="bg-white p-4 rounded-2xl shadow-sm ring-1 ring-gray-100">
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-sm font-semibold text-gray-700">Root Cause Terbanyak</h3>
          <div class="text-xs text-gray-500">Analisa</div>
        </div>
        <div class="min-h-[160px]">
          <canvas id="chartRootCause" class="w-full h-40"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

<script>
  Chart.register(ChartDataLabels);

  const dataLabelCommon = {
    color: '#111827',
    anchor: 'center',
    align: 'center',
    offset: 0,
    font: { weight: '700', size: 10 },
    formatter: v => v
  };

  const charts = {};

  function upsertChart(id, config) {
    const el = document.getElementById(id);
    if (!el) return;
    const ctx = el.getContext('2d');

    // maintainAspectRatio false supaya chart mengisi kontainer responsif
    config.options = config.options || {};
    config.options.maintainAspectRatio = false;

    if (charts[id]) {
      charts[id].config.data = config.data;
      charts[id].config.options = config.options;
      charts[id].update();
    } else {
      charts[id] = new Chart(ctx, config);
    }
  }

  // init empty charts to reserve layout
  upsertChart('chartKategori', {
    type: 'bar',
    data: { labels: [], datasets: [{ label: 'Jumlah Tiket', data: [], borderRadius: 6 }] },
    options: {
      plugins: { legend: { display: false }, datalabels: dataLabelCommon },
      scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
    }
  });

  upsertChart('chartStatus', {
    type: 'pie',
    data: { labels: [], datasets: [{ data: [] }] },
    options: {
      plugins: {
        datalabels: { color: '#fff', font: { weight: '700', size: 11 }, formatter: v => v > 0 ? v : '' }
      }
    }
  });

  upsertChart('chartTopUser', {
    type: 'bar',
    data: { labels: [], datasets: [{ data: [], borderRadius: 6 }] },
    options: {
      indexAxis: 'y',
      plugins: { legend: { display: false }, datalabels: { ...dataLabelCommon, anchor: 'center', align: 'center' } },
      scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
    }
  });

  upsertChart('chartRootCause', {
    type: 'bar',
    data: { labels: [], datasets: [{ data: [], borderRadius: 6 }] },
    options: {
      plugins: { legend: { display: false }, datalabels: dataLabelCommon },
      scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
    }
  });

  // helper show/hide loading overlay
  function showLoading(on = true) {
    const el = document.getElementById('loadingOverlay');
    if (!el) return;
    el.classList.toggle('hidden', !on);
  }

  async function fetchStats(from = '', to = '') {
    try {
      showLoading(true);
      document.getElementById('btnRefresh').disabled = true;

      const params = new URLSearchParams();
      if (from) params.set('date_from', from);
      if (to) params.set('date_to', to);
      const url = `{{ route('stats.data') }}?${params.toString()}`;
      const res = await fetch(url, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin'
      });

      if (!res.ok) {
        // tampilkan body error jika ada
        let msg = `HTTP ${res.status}`;
        try {
          const body = await res.text();
          msg += ' — ' + body;
        } catch (_) {}
        throw new Error(msg);
      }

      const json = await res.json();

      // Update KPIs jika ada (jika controller ditambah KPI, contohnya)
      // fallback ke '-' bila tidak ada
      document.getElementById('kpiTotal').textContent  = json.kpi?.total ?? '—';
      document.getElementById('kpiOpen').textContent   = json.kpi?.open ?? '—';
      document.getElementById('kpiClosed').textContent = json.kpi?.closed ?? '—';
      document.getElementById('kpiAvg').textContent    = json.kpi?.avg_resolution ?? '—';
      if (document.getElementById('kpiResponse')) {
        document.getElementById('kpiResponse').textContent = json.kpi?.avg_response ?? '—';
      }

      // update charts
      upsertChart('chartKategori', {
        type: 'bar',
        data: { labels: json.kategoriLabels, datasets: [{ data: json.kategoriData, borderRadius: 6 }] },
        options: {
          plugins: { legend: { display: false }, datalabels: { ...dataLabelCommon, anchor: 'center', align: 'center' } },
          scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
      });

      upsertChart('chartStatus', {
        type: 'pie',
        data: { labels: json.statusLabels, datasets: [{ data: json.statusData }] },
        options: {
          plugins: { datalabels: { color: '#fff', font: { weight: '700', size: 11 }, formatter: v => v > 0 ? v : '' } }
        }
      });

      upsertChart('chartTopUser', {
        type: 'bar',
        data: { labels: json.topLabels, datasets: [{ data: json.topData, borderRadius: 6 }] },
        options: {
          indexAxis: 'y',
          plugins: { legend: { display: false }, datalabels: { ...dataLabelCommon, anchor: 'center', align: 'center' } },
          scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
        }
      });

      upsertChart('chartRootCause', {
        type: 'bar',
        data: { labels: json.rootLabels, datasets: [{ data: json.rootData, borderRadius: 6 }] },
        options: {
          plugins: { legend: { display: false }, datalabels: { ...dataLabelCommon, anchor: 'center', align: 'center' } },
          scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
      });

    } catch (err) {
      console.error(err);
      alert('Terjadi kesalahan saat memuat data statistik: ' + err.message);
    } finally {
      showLoading(false);
      document.getElementById('btnRefresh').disabled = false;
    }
  }

  // events
  document.getElementById('btnRefresh').addEventListener('click', () => {
    const from = document.getElementById('filterFrom').value;
    const to = document.getElementById('filterTo').value;
    fetchStats(from, to);
  });

  document.getElementById('btnDownload').addEventListener('click', () => {
    const from = document.getElementById('filterFrom').value;
    const to = document.getElementById('filterTo').value;
    const params = new URLSearchParams();
    if (from) params.set('date_from', from);
    if (to) params.set('date_to', to);
    const url = `{{ route('it.tickets.export') }}?${params.toString()}`;
    window.location.href = url;
  });

  // initial load (no date range = all)
  fetchStats(document.getElementById('filterFrom').value, document.getElementById('filterTo').value);

  // responsiveness: reflow charts on orientation change
  window.addEventListener('orientationchange', () => {
    Object.values(charts).forEach(c => c.resize());
  });
  window.addEventListener('resize', () => {
    Object.values(charts).forEach(c => c.resize());
  });
</script>
@endsection
