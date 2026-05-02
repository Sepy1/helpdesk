@extends('layouts.app')
@section('title','Statistik Tiket')

@section('content')
<div class="w-full max-w-none py-6">
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

        <label for="filterUser" class="sr-only">Pembuat</label>
        <select id="filterUser" name="user_id" class="w-full sm:w-48 rounded-md border border-gray-200 px-3 py-2 bg-white text-sm">
          <option value="">Semua Pembuat</option>
          @foreach(($users ?? []) as $u)
            <option value="{{ $u->id }}">{{ $u->name }}</option>
          @endforeach
        </select>

        <div class="flex gap-2">
          <button id="btnRefresh" type="button" class="inline-flex items-center justify-center px-3 py-2 rounded-md bg-sky-600 text-white text-sm shadow-sm hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500" aria-label="Refresh statistik">
            <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 12a9 9 0 1 1-3-6.5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Refresh
          </button>

          <button id="btnDownload" type="button" class="inline-flex items-center justify-center px-3 py-2 rounded-md border border-gray-200 bg-white text-sm text-gray-700 hover:bg-gray-50" aria-label="Download laporan">
            <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 3v12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 11l4 4 4-4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M21 21H3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Export
          </button>

          <button type="button" id="btnReport" class="inline-flex items-center justify-center px-3 py-2 rounded-md bg-emerald-600 text-white text-sm shadow-sm hover:bg-emerald-700" title="Generate Laporan PDF">
            <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 4h16v16H4z" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 2v4" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Generate Laporan
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

        <div class="mt-4 border-t border-gray-100 pt-4">
          <div class="flex items-center justify-between gap-2 mb-2">
            <h4 class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Status Tiket</h4>
            <span class="text-[10px] text-gray-500 shrink-0">Distribusi</span>
          </div>
          <div class="flex w-full flex-col items-center gap-3 py-2">
            <div class="relative h-56 w-56 shrink-0 sm:h-64 sm:w-64 md:h-72 md:w-72">
              <canvas id="chartStatus" class="absolute inset-0 h-full w-full"></canvas>
            </div>
            <ul id="chartStatusLegend" class="flex w-full max-w-full flex-row flex-wrap items-center justify-center gap-x-4 gap-y-2 text-sm" role="list" aria-label="Legenda status tiket"></ul>
          </div>
        </div>

        <div class="mt-3 text-xs text-gray-500">Tip: ubah periode untuk melihat KPI per bulan.</div>
      </div>
    </div>

    {{-- Kategori + Top 5 pembuat + Root cause (satu card) --}}
    <div class="lg:col-span-2 md:col-span-1 col-span-1">
      <div class="bg-white p-4 rounded-2xl shadow-sm ring-1 ring-gray-100 h-full flex flex-col gap-3">
        <div class="min-w-0">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-semibold text-gray-700">Kategori Tiket Terbanyak</h3>
            <span class="text-xs text-gray-500">Top 10</span>
          </div>
          <div class="relative h-32 w-full sm:h-36 md:h-40">
            <canvas id="chartKategori" class="absolute inset-0 h-full w-full"></canvas>
          </div>
        </div>

        <div class="border-t border-gray-100 pt-3 grid grid-cols-1 gap-4 lg:grid-cols-2">
          <div class="min-w-0">
            <div class="flex items-center justify-between gap-2 mb-1.5">
              <h3 class="text-sm font-semibold text-gray-700">Top 5 User Pembuat Tiket</h3>
              <span class="text-xs text-gray-500 shrink-0">Periode</span>
            </div>
            <div class="relative h-24 w-full sm:h-28">
              <canvas id="chartTopUser" class="absolute inset-0 h-full w-full"></canvas>
            </div>
          </div>
          <div class="min-w-0">
            <div class="flex items-center justify-between gap-2 mb-1.5">
              <h3 class="text-sm font-semibold text-gray-700">Root Cause Terbanyak</h3>
              <span class="text-xs text-gray-500 shrink-0">Analisa</span>
            </div>
            <div class="relative h-24 w-full sm:h-28">
              <canvas id="chartRootCause" class="absolute inset-0 h-full w-full"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Modal pilihan sumber Executive Summary --}}
<div id="reportModeModal" class="fixed inset-0 z-[59] hidden" role="dialog" aria-modal="true" aria-labelledby="reportModeModalTitle">
  <div id="reportModeModalBackdrop" class="absolute inset-0 bg-black/40"></div>
  <div class="relative z-[60] flex min-h-full items-center justify-center p-4 pointer-events-none">
    <div class="pointer-events-auto bg-white rounded-xl shadow-xl max-w-md w-full ring-1 ring-gray-200">
      <div class="px-4 py-3 border-b border-gray-100">
        <h2 id="reportModeModalTitle" class="text-sm font-semibold text-gray-800">Pilih sumber ringkasan</h2>
      </div>
      <div class="px-4 py-3 text-sm text-gray-600">
        Pilih apakah Executive Summary akan dibuat otomatis oleh AI atau Anda isi manual.
      </div>
      <div class="px-4 py-3 border-t border-gray-100 flex flex-wrap justify-end gap-2 bg-gray-50 rounded-b-xl">
        <button type="button" id="reportModeCancel" class="px-3 py-2 rounded-md border border-gray-200 bg-white text-sm text-gray-700 hover:bg-gray-50">Batal</button>
        <button type="button" id="reportModeManual" class="px-3 py-2 rounded-md border border-emerald-200 bg-white text-emerald-700 text-sm hover:bg-emerald-50">Tanpa AI</button>
        <button type="button" id="reportModeAI" class="px-3 py-2 rounded-md bg-emerald-600 text-white text-sm shadow-sm hover:bg-emerald-700">Gunakan AI</button>
      </div>
    </div>
  </div>
</div>

{{-- Modal pratinjau Executive Summary sebelum PDF --}}
<div id="reportSummaryModal" class="fixed inset-0 z-[60] hidden" role="dialog" aria-modal="true" aria-labelledby="reportSummaryModalTitle">
  <div id="reportSummaryModalBackdrop" class="absolute inset-0 bg-black/40"></div>
  <div class="relative z-[61] flex min-h-full items-center justify-center p-4 pointer-events-none">
    <div class="pointer-events-auto bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] flex flex-col ring-1 ring-gray-200">
      <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between gap-2">
        <h2 id="reportSummaryModalTitle" class="text-sm font-semibold text-gray-800">Ringkasan eksekutif</h2>
        <button type="button" id="reportSummaryCloseX" class="rounded p-1 text-gray-500 hover:bg-gray-100" aria-label="Tutup">&times;</button>
      </div>
      <div class="px-4 py-3 flex-1 overflow-y-auto min-h-[200px]">
        <p id="reportSummaryLoading" class="text-sm text-gray-600 flex items-center gap-2">
          <svg class="animate-spin w-5 h-5 text-emerald-600 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
          Memuat ringkasan AI…
        </p>
        <label for="reportSummaryTextarea" class="sr-only">Edit ringkasan</label>
        <textarea id="reportSummaryTextarea" class="hidden w-full min-h-[260px] rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" rows="14" placeholder="Ringkasan akan muncul di sini; Anda boleh mengubah teks sebelum mengunduh PDF."></textarea>
        <p class="mt-2 text-xs text-gray-500">Sesuaikan teks jika perlu, lalu klik <strong>Generate PDF</strong> untuk mengunduh laporan.</p>
      </div>
      <div class="px-4 py-3 border-t border-gray-100 flex flex-wrap justify-end gap-2 bg-gray-50 rounded-b-xl">
        <button type="button" id="reportSummaryCancel" class="px-3 py-2 rounded-md border border-gray-200 bg-white text-sm text-gray-700 hover:bg-gray-50">Batal</button>
        <button type="button" id="reportSummaryConfirm" class="px-3 py-2 rounded-md bg-emerald-600 text-white text-sm shadow-sm hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed">Generate PDF</button>
      </div>
    </div>
  </div>
</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

<script>
  /** Gradasi donut status: diisi di afterLayout (arc sudah punya inner/outer radius), bukan scriptable backgroundColor. */
  const statusDonutGradientPlugin = {
    id: 'statusDonutGradientFill',
    afterLayout(chart) {
      if (chart._applyingDonutGradients) return;
      if (chart.config.type !== 'doughnut' || chart.canvas?.id !== 'chartStatus') return;

      const ds0 = chart.data?.datasets?.[0];
      const labels = chart.data?.labels;
      if (!ds0 || !labels?.length) {
        if (ds0) ds0._donutGradLayoutKey = '';
        return;
      }

      const data = ds0.data || [];
      const layoutKey = [chart.width, chart.height, labels.join('\0'), data.join(',')].join('|');
      if (ds0._donutGradLayoutKey === layoutKey) return;

      const meta = chart.getDatasetMeta(0);
      if (!meta?.data?.length) return;

      const { ctx } = chart;
      const fills = [];
      for (let idx = 0; idx < labels.length; idx += 1) {
        const label = labels[idx];
        const [c0, c1, c2] = statusDonutRadialStops(label, idx);
        let fill = c1;
        const arc = meta.data[idx];
        if (arc && ctx && typeof arc.getProps === 'function') {
          const p = arc.getProps(['x', 'y', 'innerRadius', 'outerRadius'], true);
          if (p) {
            const x = p.x;
            const y = p.y;
            const inner = p.innerRadius;
            const outer = p.outerRadius;
            if ([x, y, inner, outer].every((n) => Number.isFinite(Number(n))) && outer > inner + 0.5) {
              const g = ctx.createRadialGradient(x, y, inner, x, y, outer);
              g.addColorStop(0, c0);
              g.addColorStop(0.42, c1);
              g.addColorStop(1, c2);
              fill = g;
            }
          }
        }
        fills.push(fill);
      }

      if (fills.length !== labels.length) return;

      chart._applyingDonutGradients = true;
      try {
        ds0.backgroundColor = fills;
        ds0._donutGradLayoutKey = layoutKey;
      } finally {
        chart._applyingDonutGradients = false;
      }
    },
  };

  Chart.register(ChartDataLabels, statusDonutGradientPlugin);

  const dataLabelCommon = {
    color: '#111827',
    anchor: 'center',
    align: 'center',
    offset: 0,
    font: { weight: '700', size: 10 },
    formatter: v => v
  };

  /** Skala & label gaya modern (grid tipis, tanpa border sumbu tebal). */
  const modernAxisCategory = {
    grid: { display: false, drawTicks: false, drawBorder: false },
    ticks: { color: '#64748b', font: { size: 10, weight: '500' }, maxRotation: 42, autoSkipPadding: 8 },
    border: { display: false },
  };
  const modernAxisValueY = {
    beginAtZero: true,
    grid: { color: 'rgba(148, 163, 184, 0.18)', lineWidth: 1, drawBorder: false, tickLength: 0 },
    ticks: { color: '#64748b', font: { size: 10, weight: '500' }, precision: 0, padding: 6 },
    border: { display: false },
  };
  const modernAxisValueX = {
    beginAtZero: true,
    grid: { color: 'rgba(148, 163, 184, 0.18)', lineWidth: 1, drawBorder: false, tickLength: 0 },
    ticks: { color: '#64748b', font: { size: 10, weight: '500' }, precision: 0, padding: 6 },
    border: { display: false },
  };

  function barGradientVertical(chart, stops) {
    const { ctx, chartArea } = chart;
    if (!chartArea) return stops[1];
    const g = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
    g.addColorStop(0, stops[0]);
    g.addColorStop(0.55, stops[1]);
    g.addColorStop(1, stops[2]);
    return g;
  }

  function barGradientHorizontal(chart, left, right) {
    const { ctx, chartArea } = chart;
    if (!chartArea) return right;
    const g = ctx.createLinearGradient(chartArea.left, 0, chartArea.right, 0);
    g.addColorStop(0, left);
    g.addColorStop(1, right);
    return g;
  }

  /** Tiga stop gradasi radial per status (terang di dalam ring → pekat di tepi luar), selaras gaya chart batang. */
  function statusDonutRadialStops(label, idx) {
    const key = String(label || '');
    const map = {
      OPEN: ['rgba(254, 215, 215, 0.95)', 'rgba(248, 113, 113, 0.98)', 'rgba(153, 27, 27, 1)'],
      ON_PROGRESS: ['rgba(254, 243, 199, 0.95)', 'rgba(251, 191, 36, 0.98)', 'rgba(180, 83, 9, 1)'],
      TAKEN: ['rgba(255, 237, 213, 0.95)', 'rgba(251, 146, 60, 0.98)', 'rgba(194, 65, 12, 1)'],
      ESKALASI_VENDOR: ['rgba(250, 232, 255, 0.95)', 'rgba(232, 121, 249, 0.98)', 'rgba(126, 34, 206, 1)'],
      VENDOR_RESOLVED: ['rgba(224, 231, 255, 0.95)', 'rgba(129, 140, 248, 0.98)', 'rgba(55, 48, 163, 1)'],
      CLOSED: ['rgba(209, 250, 229, 0.95)', 'rgba(52, 211, 153, 0.98)', 'rgba(5, 46, 22, 0.96)'],
    };
    if (map[key]) return map[key];
    const fallbacks = [
      ['rgba(199, 210, 254, 0.95)', 'rgba(99, 102, 241, 0.98)', 'rgba(49, 46, 129, 1)'],
      ['rgba(186, 230, 253, 0.95)', 'rgba(14, 165, 233, 0.98)', 'rgba(7, 89, 133, 1)'],
      ['rgba(233, 213, 255, 0.95)', 'rgba(168, 85, 247, 0.98)', 'rgba(88, 28, 135, 1)'],
    ];
    return fallbacks[idx % fallbacks.length];
  }

  /** Fallback solid sebelum plugin gradasi jalan (satu frame / data kosong). */
  function statusDonutSolidFills(labels) {
    return (labels || []).map((lbl, i) => statusDonutRadialStops(lbl, i)[1]);
  }

  const STATUS_LEGEND_LABEL = {
    OPEN: 'Open',
    ON_PROGRESS: 'Dalam proses',
    TAKEN: 'Diambil TI',
    ESKALASI_VENDOR: 'Eskalasi vendor',
    VENDOR_RESOLVED: 'Selesai vendor',
    CLOSED: 'Selesai',
  };

  function statusLegendText(raw) {
    const k = String(raw || '');
    if (STATUS_LEGEND_LABEL[k]) return STATUS_LEGEND_LABEL[k];
    return k.replace(/_/g, ' ').trim() || '—';
  }

  /** Legenda di luar donut: swatch warna (selaras palet chart) + label + jumlah. */
  function renderChartStatusLegend(labels, data) {
    const ul = document.getElementById('chartStatusLegend');
    if (!ul) return;
    ul.replaceChildren();
    if (!labels?.length) return;

    labels.forEach((raw, i) => {
      const n = Number(data[i] ?? 0);
      const swatchColor = statusDonutRadialStops(raw, i)[1];
      const li = document.createElement('li');
      li.className = 'inline-flex shrink-0 items-center gap-1.5';
      li.setAttribute('role', 'listitem');

      const sw = document.createElement('span');
      sw.className = 'h-3.5 w-3.5 shrink-0 rounded-md ring-1 ring-black/10 shadow-sm';
      sw.style.background = swatchColor;
      sw.setAttribute('aria-hidden', 'true');

      const lab = document.createElement('span');
      lab.className = 'whitespace-nowrap font-medium leading-snug text-gray-700';
      lab.textContent = statusLegendText(raw);

      const num = document.createElement('span');
      num.className = 'shrink-0 tabular-nums text-xs font-semibold text-gray-500';
      num.textContent = String(n);

      li.append(sw, lab, num);
      ul.appendChild(li);
    });
  }

  const modernDonutDataset = {
    borderWidth: 2,
    borderColor: '#ffffff',
    hoverBorderWidth: 2,
    hoverBorderColor: '#ffffff',
  };

  // Doughnut: matikan datalabels lewat display:false (lebih kompatibel daripada plugins.datalabels = false).
  const modernDonutOptions = {
    cutout: '60%',
    rotation: -90,
    plugins: {
      legend: { display: false },
      datalabels: { display: false },
    },
  };

  const charts = {};

  function upsertChart(id, config) {
    const el = document.getElementById(id);
    if (!el) return;
    const ctx = el.getContext('2d');

    config.options = config.options || {};
    config.options.maintainAspectRatio = false;

    // Hancurkan instance lalu buat ulang — update in-place sering membuat Chart.js 4 + plugin tidak sinkron (data kosong).
    if (charts[id]) {
      try {
        charts[id].destroy();
      } catch (_) {}
      charts[id] = null;
    }
    try {
      charts[id] = new Chart(ctx, config);
    } catch (e) {
      console.error('Chart init gagal:', id, e);
      charts[id] = null;
    }
  }

  const kategoriBarRadii = { topLeft: 10, topRight: 10, bottomLeft: 3, bottomRight: 3 };
  const topUserBarRadii = { topLeft: 0, topRight: 10, bottomLeft: 0, bottomRight: 10 };
  const rootBarRadii = { topLeft: 10, topRight: 10, bottomLeft: 3, bottomRight: 3 };

  // init empty charts to reserve layout
  upsertChart('chartKategori', {
    type: 'bar',
    data: {
      labels: [],
      datasets: [{
        label: 'Jumlah Tiket',
        data: [],
        borderWidth: 0,
        borderRadius: kategoriBarRadii,
        maxBarThickness: 38,
        backgroundColor: (c) => barGradientVertical(c.chart, ['rgba(165, 180, 252, 0.5)', 'rgba(99, 102, 241, 0.88)', 'rgba(49, 46, 129, 0.95)']),
      }],
    },
    options: {
      plugins: {
        legend: { display: false },
        datalabels: {
          color: '#334155',
          anchor: 'end',
          align: 'top',
          offset: 4,
          font: { weight: '700', size: 9 },
          formatter: (v) => v,
        },
      },
      scales: { x: modernAxisCategory, y: modernAxisValueY },
    },
  });

  upsertChart('chartStatus', {
    type: 'doughnut',
    data: {
      labels: [],
      datasets: [{
        data: [],
        backgroundColor: statusDonutSolidFills([]),
        ...modernDonutDataset,
      }],
    },
    options: { ...modernDonutOptions },
  });

  upsertChart('chartTopUser', {
    type: 'bar',
    data: {
      labels: [],
      datasets: [{
        data: [],
        borderWidth: 0,
        borderRadius: topUserBarRadii,
        maxBarThickness: 22,
        backgroundColor: (c) => barGradientHorizontal(c.chart, 'rgba(125, 211, 252, 0.55)', 'rgba(3, 105, 161, 0.92)'),
      }],
    },
    options: {
      indexAxis: 'y',
      plugins: {
        legend: { display: false },
        datalabels: {
          color: '#fff',
          anchor: 'center',
          align: 'center',
          font: { weight: '700', size: 9 },
          formatter: (v) => v,
        },
      },
      scales: { x: modernAxisValueX, y: modernAxisCategory },
    },
  });

  upsertChart('chartRootCause', {
    type: 'bar',
    data: {
      labels: [],
      datasets: [{
        data: [],
        borderWidth: 0,
        borderRadius: rootBarRadii,
        maxBarThickness: 34,
        backgroundColor: (c) => barGradientVertical(c.chart, ['rgba(110, 231, 183, 0.45)', 'rgba(16, 185, 129, 0.88)', 'rgba(6, 78, 59, 0.92)']),
      }],
    },
    options: {
      plugins: {
        legend: { display: false },
        datalabels: {
          color: '#134e4a',
          anchor: 'end',
          align: 'top',
          offset: 4,
          font: { weight: '700', size: 9 },
          formatter: (v) => v,
        },
      },
      scales: { x: modernAxisCategory, y: modernAxisValueY },
    },
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
      const user = document.getElementById('filterUser')?.value;
      if (user) params.set('user_id', user);
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
        data: {
          labels: json.kategoriLabels,
          datasets: [{
            label: 'Jumlah Tiket',
            data: json.kategoriData,
            borderWidth: 0,
            borderRadius: kategoriBarRadii,
            maxBarThickness: 38,
            backgroundColor: (c) => barGradientVertical(c.chart, ['rgba(165, 180, 252, 0.5)', 'rgba(99, 102, 241, 0.88)', 'rgba(49, 46, 129, 0.95)']),
          }],
        },
        options: {
          plugins: {
            legend: { display: false },
            datalabels: {
              color: '#334155',
              anchor: 'end',
              align: 'top',
              offset: 4,
              font: { weight: '700', size: 9 },
              formatter: (v) => v,
            },
          },
          scales: { x: modernAxisCategory, y: modernAxisValueY },
        },
      });

      const statusLabels = json.statusLabels || [];
      const statusData = (json.statusData || []).map((n) => Number(n));
      upsertChart('chartStatus', {
        type: 'doughnut',
        data: {
          labels: statusLabels,
          datasets: [{
            data: statusData,
            backgroundColor: statusDonutSolidFills(statusLabels),
            ...modernDonutDataset,
          }],
        },
        options: { ...modernDonutOptions },
      });
      requestAnimationFrame(() => {
        try {
          if (charts.chartStatus && typeof charts.chartStatus.resize === 'function') {
            charts.chartStatus.resize();
          }
        } catch (_) {}
      });
      renderChartStatusLegend(statusLabels, statusData);

      upsertChart('chartTopUser', {
        type: 'bar',
        data: {
          labels: json.topLabels,
          datasets: [{
            data: json.topData,
            borderWidth: 0,
            borderRadius: topUserBarRadii,
            maxBarThickness: 22,
            backgroundColor: (c) => barGradientHorizontal(c.chart, 'rgba(125, 211, 252, 0.55)', 'rgba(3, 105, 161, 0.92)'),
          }],
        },
        options: {
          indexAxis: 'y',
          plugins: {
            legend: { display: false },
            datalabels: {
              color: '#fff',
              anchor: 'center',
              align: 'center',
              font: { weight: '700', size: 9 },
              formatter: (v) => v,
            },
          },
          scales: { x: modernAxisValueX, y: modernAxisCategory },
        },
      });

      upsertChart('chartRootCause', {
        type: 'bar',
        data: {
          labels: json.rootLabels,
          datasets: [{
            data: json.rootData,
            borderWidth: 0,
            borderRadius: rootBarRadii,
            maxBarThickness: 34,
            backgroundColor: (c) => barGradientVertical(c.chart, ['rgba(110, 231, 183, 0.45)', 'rgba(16, 185, 129, 0.88)', 'rgba(6, 78, 59, 0.92)']),
          }],
        },
        options: {
          plugins: {
            legend: { display: false },
            datalabels: {
              color: '#134e4a',
              anchor: 'end',
              align: 'top',
              offset: 4,
              font: { weight: '700', size: 9 },
              formatter: (v) => v,
            },
          },
          scales: { x: modernAxisCategory, y: modernAxisValueY },
        },
      });

    } catch (err) {
      console.error(err);
      renderChartStatusLegend([], []);
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
    const user = document.getElementById('filterUser')?.value;
    if (user) params.set('user_id', user);
    const url = `{{ route('it.tickets.export') }}?${params.toString()}`;
    window.location.href = url;
  });

  function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  }

  const reportModeModal = document.getElementById('reportModeModal');
  const reportSummaryModal = document.getElementById('reportSummaryModal');
  const reportSummaryLoading = document.getElementById('reportSummaryLoading');
  const reportSummaryTextarea = document.getElementById('reportSummaryTextarea');
  const reportSummaryConfirm = document.getElementById('reportSummaryConfirm');

  function openReportModeModal() {
    reportModeModal.classList.remove('hidden');
  }

  function closeReportModeModal() {
    reportModeModal.classList.add('hidden');
  }

  function openReportSummaryModal() {
    reportSummaryModal.classList.remove('hidden');
    reportSummaryLoading.classList.remove('hidden');
    reportSummaryTextarea.classList.add('hidden');
    reportSummaryTextarea.value = '';
    reportSummaryConfirm.disabled = true;
  }

  function closeReportSummaryModal() {
    reportSummaryModal.classList.add('hidden');
  }

  async function loadSummaryFromAI() {
    openReportSummaryModal();
    const from = document.getElementById('filterFrom').value;
    const to = document.getElementById('filterTo').value;
    const user = document.getElementById('filterUser')?.value || '';

    try {
      const res = await fetch(`{{ route('it.stats.report.summary_preview') }}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken(),
          'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        body: JSON.stringify({
          date_from: from || null,
          date_to: to || null,
          user_id: user || null,
        }),
      });
      const json = await res.json().catch(() => ({}));
      if (!res.ok) {
        throw new Error(json.message || `HTTP ${res.status}`);
      }
      reportSummaryTextarea.value = (json.executive_summary || '').trim();
      if (json.ai_unavailable && !reportSummaryTextarea.value) {
        reportSummaryTextarea.value = 'Ringkasan otomatis tidak tersedia. Silakan tulis ringkasan eksekutif di sini, lalu klik Generate PDF.';
      }
    } catch (err) {
      console.error(err);
      alert('Gagal memuat ringkasan AI: ' + err.message);
      reportSummaryTextarea.value = 'Silakan tulis ringkasan eksekutif di sini, lalu klik Generate PDF.';
    } finally {
      reportSummaryLoading.classList.add('hidden');
      reportSummaryTextarea.classList.remove('hidden');
      reportSummaryConfirm.disabled = false;
      reportSummaryTextarea.focus();
    }
  }

  function openManualSummary() {
    openReportSummaryModal();
    reportSummaryLoading.classList.add('hidden');
    reportSummaryTextarea.classList.remove('hidden');
    reportSummaryTextarea.value = '';
    reportSummaryTextarea.placeholder = 'Silakan tulis Executive Summary secara manual di sini.';
    reportSummaryConfirm.disabled = false;
    reportSummaryTextarea.focus();
  }

  document.getElementById('btnReport').addEventListener('click', () => {
    openReportModeModal();
  });

  document.getElementById('reportModeCancel').addEventListener('click', closeReportModeModal);
  document.getElementById('reportModeModalBackdrop').addEventListener('click', closeReportModeModal);
  document.getElementById('reportModeManual').addEventListener('click', () => {
    closeReportModeModal();
    openManualSummary();
  });
  document.getElementById('reportModeAI').addEventListener('click', async () => {
    closeReportModeModal();
    await loadSummaryFromAI();
  });

  document.getElementById('reportSummaryCancel').addEventListener('click', closeReportSummaryModal);
  document.getElementById('reportSummaryCloseX').addEventListener('click', closeReportSummaryModal);
  document.getElementById('reportSummaryModalBackdrop').addEventListener('click', closeReportSummaryModal);

  reportSummaryConfirm.addEventListener('click', async () => {
    const from = document.getElementById('filterFrom').value;
    const to = document.getElementById('filterTo').value;
    const user = document.getElementById('filterUser')?.value || '';
    const executiveSummary = reportSummaryTextarea.value;

    reportSummaryConfirm.disabled = true;
    try {
      const fd = new FormData();
      fd.append('_token', csrfToken());
      fd.append('date_from', from);
      fd.append('date_to', to);
      fd.append('user_id', user);
      fd.append('executive_summary', executiveSummary);

      const res = await fetch(`{{ route('it.stats.report') }}`, {
        method: 'POST',
        body: fd,
        headers: {
          'Accept': 'application/pdf, text/html',
          'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
      });

      const ct = (res.headers.get('content-type') || '').toLowerCase();
      const blob = await res.blob();

      if (!res.ok) {
        const t = await blob.text().catch(() => '');
        throw new Error(t.slice(0, 240) || `HTTP ${res.status}`);
      }

      if (ct.includes('application/pdf')) {
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'laporan_tiket.pdf';
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
        closeReportSummaryModal();
      } else {
        const htmlUrl = URL.createObjectURL(blob);
        window.open(htmlUrl, '_blank');
        URL.revokeObjectURL(htmlUrl);
        closeReportSummaryModal();
      }
    } catch (err) {
      console.error(err);
      alert('Gagal membuat laporan: ' + err.message);
    } finally {
      reportSummaryConfirm.disabled = false;
    }
  });

  // Muat data setelah chart pertama punya kesempatan layout (hindari error yang memblok fetch).
  function runInitialFetchStats() {
    const fromEl = document.getElementById('filterFrom');
    const toEl = document.getElementById('filterTo');
    if (!fromEl || !toEl) return;
    fetchStats(fromEl.value, toEl.value);
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => requestAnimationFrame(runInitialFetchStats));
  } else {
    requestAnimationFrame(runInitialFetchStats);
  }

  // responsiveness: reflow charts on orientation change
  window.addEventListener('orientationchange', () => {
    Object.values(charts).forEach((c) => {
      try {
        if (c && typeof c.resize === 'function') c.resize();
      } catch (_) {}
    });
  });
  window.addEventListener('resize', () => {
    Object.values(charts).forEach((c) => {
      try {
        if (c && typeof c.resize === 'function') c.resize();
      } catch (_) {}
    });
  });
</script>
@endsection
