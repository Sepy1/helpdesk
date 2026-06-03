<?php $__env->startSection('title','Report Statistik Tiket'); ?>

<?php $__env->startSection('content'); ?>
<div class="w-full max-w-none pb-8">
  <div class="space-y-4">
    <section class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
      <div class="min-w-0">
        <div class="flex flex-wrap items-center gap-2 text-xs font-medium text-slate-500">
          <span class="rounded-md border border-blue-100 bg-blue-50 px-2 py-1 text-blue-700">IT Report</span>
          <span id="generatedAt">Memuat data...</span>
        </div>
        <h1 class="mt-2 text-2xl font-semibold tracking-normal text-slate-900 sm:text-3xl">Report Statistik Tiket</h1>
        <p class="mt-1 max-w-3xl text-sm text-slate-500">Monitoring volume, status, backlog, performa respon, dan root cause layanan helpdesk.</p>
      </div>

      <div class="flex flex-wrap gap-2">
        <button id="btnDownload" type="button" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500" title="Export data tiket">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M12 3v12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 11l4 4 4-4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M4 21h16" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          Export
        </button>
        <button type="button" id="btnReport" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-emerald-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500" title="Generate laporan PDF">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M7 3h7l5 5v13H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 3v6h5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 14h6M9 18h4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          Generate Laporan
        </button>
      </div>
    </section>

    <section class="rounded-lg border border-slate-200 bg-white p-3 shadow-sm">
      <form id="filterForm" class="grid grid-cols-1 gap-3 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,1.4fr)_auto] lg:items-end">
        <div>
          <label for="filterFrom" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Dari tanggal</label>
          <input id="filterFrom" name="date_from" type="date" class="w-full rounded-lg border-slate-200 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>
        <div>
          <label for="filterTo" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Sampai tanggal</label>
          <input id="filterTo" name="date_to" type="date" class="w-full rounded-lg border-slate-200 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>
        <div>
          <label for="filterKodeKantor" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Kantor pembuat</label>
          <select id="filterKodeKantor" name="kode_kantor" class="w-full rounded-lg border-slate-200 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <option value="">Semua Kantor</option>
            <?php $__currentLoopData = ($kodeKantors ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($k->kode); ?>"><?php echo e($k->kode); ?> - <?php echo e($k->nama_kantor); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div class="flex gap-2">
          <button id="btnRefresh" type="submit" class="inline-flex min-h-10 flex-1 items-center justify-center gap-2 rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 lg:flex-none" title="Refresh statistik">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M21 12a9 9 0 1 1-2.64-6.36" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M21 3v6h-6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Refresh
          </button>
          <button id="btnReset" type="button" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500" title="Reset filter">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span class="sr-only">Reset filter</span>
          </button>
        </div>
      </form>
    </section>

    <section class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-6">
      <article class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between gap-3">
          <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total tiket</p>
          <span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span>
        </div>
        <div id="kpiTotal" class="mt-3 text-3xl font-semibold text-slate-950">-</div>
        <p id="kpiTotalSub" class="mt-1 text-xs text-slate-500">Semua data</p>
      </article>

      <article class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between gap-3">
          <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tiket aktif</p>
          <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
        </div>
        <div id="kpiOpen" class="mt-3 text-3xl font-semibold text-slate-950">-</div>
        <p id="kpiActiveSub" class="mt-1 text-xs text-slate-500">Butuh follow-up</p>
      </article>

      <article class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between gap-3">
          <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tiket selesai</p>
          <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
        </div>
        <div id="kpiClosed" class="mt-3 text-3xl font-semibold text-slate-950">-</div>
        <p id="kpiClosedSub" class="mt-1 text-xs text-slate-500">Tertutup</p>
      </article>

      <article class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between gap-3">
          <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Completion</p>
          <span class="h-2.5 w-2.5 rounded-full bg-violet-500"></span>
        </div>
        <div id="kpiCompletion" class="mt-3 text-3xl font-semibold text-slate-950">-</div>
        <div class="mt-3 h-2 rounded-full bg-slate-100">
          <div id="completionBar" class="h-2 rounded-full bg-violet-500" style="width:0%"></div>
        </div>
      </article>

      <article class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between gap-3">
          <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Respon TI</p>
          <span class="h-2.5 w-2.5 rounded-full bg-cyan-500"></span>
        </div>
        <div id="kpiResponse" class="mt-3 text-2xl font-semibold text-slate-950">-</div>
        <p id="kpiResponseSub" class="mt-1 text-xs text-slate-500">Rata-rata ambil tiket</p>
      </article>

      <article class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between gap-3">
          <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Penyelesaian</p>
          <span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span>
        </div>
        <div id="kpiAvg" class="mt-3 text-2xl font-semibold text-slate-950">-</div>
        <p id="kpiAvgSub" class="mt-1 text-xs text-slate-500">Rata-rata closed</p>
      </article>
    </section>

    <section class="grid grid-cols-1 gap-4 xl:grid-cols-12">
      <article class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm xl:col-span-8">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
          <div>
            <h2 id="trendTitle" class="text-base font-semibold text-slate-900">Tren Volume Tiket</h2>
            <p id="trendCaption" class="text-sm text-slate-500">30 hari terakhir</p>
          </div>
          <div class="flex flex-wrap items-center gap-2 sm:justify-end">
            <label for="trendMode" class="sr-only">Jenis tren</label>
            <select id="trendMode" class="h-9 rounded-lg border-slate-200 bg-white pl-3 pr-8 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500">
              <option value="volume">Volume tiket</option>
              <option value="kategori">Kategori</option>
              <option value="subkategori">Sub kategori</option>
              <option value="root_cause">Root cause</option>
            </select>
            <span id="periodLabel" class="inline-flex h-9 w-fit items-center rounded-md border border-slate-200 px-2 py-1 text-xs font-medium text-slate-600">Semua data</span>
          </div>
        </div>
        <div class="relative mt-4 h-72 w-full">
          <canvas id="chartTrend" class="absolute inset-0 h-full w-full"></canvas>
          <div id="chartTrendEmpty" class="absolute inset-0 hidden items-center justify-center rounded-lg border border-dashed border-slate-200 bg-slate-50 text-sm text-slate-500">Belum ada data tren.</div>
        </div>
      </article>

      <article class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm xl:col-span-4">
        <div class="flex items-start justify-between gap-3">
          <div>
            <h2 class="text-base font-semibold text-slate-900">Status Saat Ini</h2>
            <p id="scopeLabel" class="text-sm text-slate-500">Semua kantor</p>
          </div>
          <span id="statusHealthBadge" class="rounded-md border border-slate-200 px-2 py-1 text-xs font-medium text-slate-600">-</span>
        </div>
        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-[12rem_minmax(0,1fr)] xl:grid-cols-1">
          <div class="relative mx-auto h-48 w-48">
            <canvas id="chartStatus" class="absolute inset-0 h-full w-full"></canvas>
            <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
              <span id="statusCenterTotal" class="text-3xl font-semibold text-slate-950">-</span>
              <span class="text-xs font-medium uppercase tracking-wide text-slate-500">tiket</span>
            </div>
          </div>
          <div>
            <ul id="chartStatusLegend" class="flex flex-wrap gap-2 text-xs" role="list" aria-label="Legenda status tiket"></ul>
            <div id="statusList" class="mt-3 space-y-2"></div>
          </div>
        </div>
      </article>
    </section>

    <section class="grid grid-cols-1 gap-4 xl:grid-cols-3">
      <article class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between gap-3">
          <div>
            <h2 class="text-base font-semibold text-slate-900">Kategori Terbanyak</h2>
            <p class="text-sm text-slate-500">Top 10 kategori tiket</p>
          </div>
          <span class="rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700">Kategori</span>
        </div>
        <div id="categoryList" class="mt-4 space-y-3"></div>
      </article>

      <article class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between gap-3">
          <div>
            <h2 class="text-base font-semibold text-slate-900">Top Kantor Pembuat</h2>
            <p class="text-sm text-slate-500">Kantor dengan volume tertinggi</p>
          </div>
          <span class="rounded-md bg-cyan-50 px-2 py-1 text-xs font-medium text-cyan-700">Kantor</span>
        </div>
        <div id="officeList" class="mt-4 space-y-3"></div>
      </article>

      <article class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between gap-3">
          <div>
            <h2 class="text-base font-semibold text-slate-900">Root Cause</h2>
            <p class="text-sm text-slate-500">Penyebab terbanyak pada tiket selesai</p>
          </div>
          <span class="rounded-md bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700">Analisa</span>
        </div>
        <div id="rootList" class="mt-4 space-y-3"></div>
      </article>
    </section>

    <section class="grid grid-cols-1 gap-4 xl:grid-cols-12">
      <article class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm xl:col-span-5">
        <div class="flex items-start justify-between gap-3">
          <div>
            <h2 class="text-base font-semibold text-slate-900">Aging Backlog</h2>
            <p class="text-sm text-slate-500">Umur tiket aktif berdasarkan tanggal dibuat</p>
          </div>
          <span id="agingCriticalBadge" class="rounded-md border border-slate-200 px-2 py-1 text-xs font-medium text-slate-600">-</span>
        </div>
        <div id="agingList" class="mt-4 space-y-3"></div>
      </article>

      <article class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm xl:col-span-7">
        <div class="flex items-start justify-between gap-3">
          <div>
            <h2 class="text-base font-semibold text-slate-900">Insight Operasional</h2>
            <p class="text-sm text-slate-500">Ringkasan kondisi berdasarkan filter aktif</p>
          </div>
          <span id="insightCount" class="rounded-md bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700">-</span>
        </div>
        <div id="insightList" class="mt-4 grid grid-cols-1 gap-3 lg:grid-cols-2"></div>
      </article>
    </section>
  </div>
</div>

<div id="loadingOverlay" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/35 p-4">
  <div class="flex w-full max-w-sm items-center gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-xl">
    <svg class="h-6 w-6 animate-spin text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v8z"></path></svg>
    <div>
      <div class="text-sm font-semibold text-slate-900">Memuat statistik</div>
      <div class="text-xs text-slate-500">Menyiapkan report terbaru.</div>
    </div>
  </div>
</div>

<div id="reportModeModal" class="fixed inset-0 z-[59] hidden" role="dialog" aria-modal="true" aria-labelledby="reportModeModalTitle">
  <div id="reportModeModalBackdrop" class="absolute inset-0 bg-slate-950/45"></div>
  <div class="relative z-[60] flex min-h-full items-center justify-center p-4 pointer-events-none">
    <div class="pointer-events-auto w-full max-w-md rounded-lg border border-slate-200 bg-white shadow-xl">
      <div class="border-b border-slate-100 px-4 py-3">
        <h2 id="reportModeModalTitle" class="text-base font-semibold text-slate-900">Pilih sumber ringkasan</h2>
      </div>
      <div class="px-4 py-3 text-sm text-slate-600">
        Executive Summary bisa dibuat otomatis oleh AI atau ditulis manual sebelum PDF diunduh.
      </div>
      <div class="flex flex-wrap justify-end gap-2 rounded-b-lg border-t border-slate-100 bg-slate-50 px-4 py-3">
        <button type="button" id="reportModeCancel" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
        <button type="button" id="reportModeManual" class="rounded-lg border border-emerald-200 bg-white px-3 py-2 text-sm font-medium text-emerald-700 hover:bg-emerald-50">Tanpa AI</button>
        <button type="button" id="reportModeAI" class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-700">Gunakan AI</button>
      </div>
    </div>
  </div>
</div>

<div id="reportSummaryModal" class="fixed inset-0 z-[60] hidden" role="dialog" aria-modal="true" aria-labelledby="reportSummaryModalTitle">
  <div id="reportSummaryModalBackdrop" class="absolute inset-0 bg-slate-950/45"></div>
  <div class="relative z-[61] flex min-h-full items-center justify-center p-4 pointer-events-none">
    <div class="pointer-events-auto flex max-h-[90vh] w-full max-w-2xl flex-col rounded-lg border border-slate-200 bg-white shadow-xl">
      <div class="flex items-center justify-between gap-2 border-b border-slate-100 px-4 py-3">
        <h2 id="reportSummaryModalTitle" class="text-base font-semibold text-slate-900">Ringkasan eksekutif</h2>
        <button type="button" id="reportSummaryCloseX" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100" aria-label="Tutup">&times;</button>
      </div>
      <div class="min-h-[220px] flex-1 overflow-y-auto px-4 py-3">
        <p id="reportSummaryLoading" class="flex items-center gap-2 text-sm text-slate-600">
          <svg class="h-5 w-5 animate-spin text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v8z"></path></svg>
          Memuat ringkasan AI...
        </p>
        <label for="reportSummaryTextarea" class="sr-only">Edit ringkasan</label>
        <textarea id="reportSummaryTextarea" class="hidden min-h-[260px] w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-emerald-500 focus:ring-emerald-500" rows="14" placeholder="Ringkasan akan muncul di sini; Anda boleh mengubah teks sebelum mengunduh PDF."></textarea>
        <p class="mt-2 text-xs text-slate-500">Sesuaikan teks jika perlu, lalu klik <strong>Generate PDF</strong>.</p>
      </div>
      <div class="flex flex-wrap justify-end gap-2 rounded-b-lg border-t border-slate-100 bg-slate-50 px-4 py-3">
        <button type="button" id="reportSummaryCancel" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
        <button type="button" id="reportSummaryConfirm" class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50">Generate PDF</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

<script>
  if (window.Chart && window.ChartDataLabels) {
    Chart.register(window.ChartDataLabels);
  }

  const charts = {};
  let latestStats = null;
  const nf = new Intl.NumberFormat('id-ID');
  const pf = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 1 });

  const STATUS_META = {
    OPEN: { label: 'Open', color: '#ef4444', soft: 'bg-red-50 text-red-700 border-red-100' },
    ON_PROGRESS: { label: 'Dalam proses', color: '#f59e0b', soft: 'bg-amber-50 text-amber-700 border-amber-100' },
    ESKALASI_VENDOR: { label: 'Eskalasi vendor', color: '#8b5cf6', soft: 'bg-violet-50 text-violet-700 border-violet-100' },
    VENDOR_RESOLVED: { label: 'Selesai vendor', color: '#06b6d4', soft: 'bg-cyan-50 text-cyan-700 border-cyan-100' },
    CLOSED: { label: 'Selesai', color: '#10b981', soft: 'bg-emerald-50 text-emerald-700 border-emerald-100' },
  };
  const TREND_META = {
    volume: { title: 'Tren Volume Tiket' },
    kategori: { title: 'Tren Berdasarkan Kategori' },
    subkategori: { title: 'Tren Berdasarkan Sub Kategori' },
    root_cause: { title: 'Tren Berdasarkan Root Cause' },
  };
  const TREND_COLORS = [
    { border: '#2563eb', background: 'rgba(37, 99, 235, 0.12)' },
    { border: '#10b981', background: 'rgba(16, 185, 129, 0.10)' },
    { border: '#f59e0b', background: 'rgba(245, 158, 11, 0.10)' },
    { border: '#8b5cf6', background: 'rgba(139, 92, 246, 0.10)' },
    { border: '#ef4444', background: 'rgba(239, 68, 68, 0.10)' },
  ];

  function number(value) {
    return nf.format(Number(value) || 0);
  }

  function percent(value) {
    return `${pf.format(Number(value) || 0)}%`;
  }

  function setText(id, value) {
    const el = document.getElementById(id);
    if (el) el.textContent = value;
  }

  function setWidth(id, value) {
    const el = document.getElementById(id);
    if (!el) return;
    const width = Math.max(0, Math.min(100, Number(value) || 0));
    el.style.width = `${width}%`;
  }

  function makeEl(tag, className = '', text = undefined) {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (text !== undefined) node.textContent = text;
    return node;
  }

  function statusLabel(status) {
    const key = String(status || '');
    return STATUS_META[key]?.label || key.replace(/_/g, ' ') || '-';
  }

  function statusColor(status, index = 0) {
    const fallback = ['#2563eb', '#64748b', '#db2777', '#14b8a6'];
    return STATUS_META[String(status || '')]?.color || fallback[index % fallback.length];
  }

  function showLoading(on = true) {
    const el = document.getElementById('loadingOverlay');
    if (!el) return;
    el.classList.toggle('hidden', !on);
    el.classList.toggle('flex', on);
  }

  function toggleEmpty(id, isEmpty) {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.toggle('hidden', !isEmpty);
    el.classList.toggle('flex', isEmpty);
  }

  function hasChartData(values) {
    return (values || []).some((value) => Number(value) > 0);
  }

  function hasDatasetData(datasets) {
    return (datasets || []).some((dataset) => hasChartData(dataset.data || []));
  }

  function upsertChart(id, config) {
    const canvas = document.getElementById(id);
    if (!canvas || !window.Chart) return;
    if (charts[id]) {
      try { charts[id].destroy(); } catch (_) {}
    }

    config.options = config.options || {};
    config.options.maintainAspectRatio = false;
    config.options.responsive = true;

    charts[id] = new Chart(canvas.getContext('2d'), config);
  }

  const chartGrid = 'rgba(148, 163, 184, 0.22)';
  const valueAxis = {
    beginAtZero: true,
    grid: { color: chartGrid, drawBorder: false },
    ticks: { color: '#64748b', precision: 0, font: { size: 11 } },
    border: { display: false },
  };

  function selectedTrendMode() {
    const mode = document.getElementById('trendMode')?.value || 'volume';
    return TREND_META[mode] ? mode : 'volume';
  }

  function trendDataset(row, index, fill = false) {
    const color = TREND_COLORS[index % TREND_COLORS.length];

    return {
      label: row.label || '-',
      data: row.data || [],
      borderColor: color.border,
      backgroundColor: color.background,
      fill,
      tension: 0.35,
      borderWidth: 2,
      pointRadius: 2,
      pointHoverRadius: 4,
    };
  }

  function trendDatasets(json, mode) {
    const trend = json?.trend || {};
    if (mode === 'volume') {
      return [
        trendDataset({ label: 'Tiket masuk', data: trend.created || [] }, 0, true),
        trendDataset({ label: 'Tiket selesai', data: trend.closed || [] }, 1, true),
      ];
    }

    return (trend.breakdowns?.[mode]?.datasets || [])
      .map((row, index) => trendDataset(row, index, false));
  }

  function renderTrendChart(json) {
    const mode = selectedTrendMode();
    const labels = json?.trend?.labels || [];
    const datasets = trendDatasets(json, mode);

    setText('trendTitle', TREND_META[mode]?.title || TREND_META.volume.title);
    toggleEmpty('chartTrendEmpty', !hasDatasetData(datasets));

    upsertChart('chartTrend', {
      type: 'line',
      data: {
        labels,
        datasets,
      },
      options: {
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: { position: 'top', labels: { boxWidth: 10, boxHeight: 10, color: '#334155', font: { size: 12 } } },
          datalabels: { display: false },
        },
        scales: {
          x: { grid: { display: false }, ticks: { color: '#64748b', maxRotation: 0, autoSkip: true, font: { size: 11 } }, border: { display: false } },
          y: valueAxis,
        },
      },
    });
  }

  function donutChart(rows) {
    const labels = (rows || []).map((row) => statusLabel(row.status));
    const data = (rows || []).map((row) => Number(row.total) || 0);
    upsertChart('chartStatus', {
      type: 'doughnut',
      data: {
        labels,
        datasets: [{
          data,
          backgroundColor: (rows || []).map((row, index) => statusColor(row.status, index)),
          borderColor: '#ffffff',
          borderWidth: 3,
          hoverBorderColor: '#ffffff',
        }],
      },
      options: {
        cutout: '68%',
        plugins: {
          legend: { display: false },
          datalabels: { display: false },
        },
      },
    });
  }

  function renderStatus(rows, kpi) {
    const legend = document.getElementById('chartStatusLegend');
    const list = document.getElementById('statusList');
    if (legend) legend.replaceChildren();
    if (list) list.replaceChildren();

    if (!rows?.length) {
      if (list) list.appendChild(makeEl('div', 'rounded-lg border border-dashed border-slate-200 bg-slate-50 px-3 py-4 text-sm text-slate-500', 'Belum ada data status.'));
      return;
    }

    rows.forEach((row, index) => {
      const color = statusColor(row.status, index);

      if (legend) {
        const item = makeEl('li', 'inline-flex items-center gap-1 rounded-md border border-slate-200 bg-white px-2 py-1');
        const swatch = makeEl('span', 'h-2.5 w-2.5 rounded-full');
        swatch.style.backgroundColor = color;
        item.append(swatch, makeEl('span', 'font-medium text-slate-700', statusLabel(row.status)));
        legend.appendChild(item);
      }

      if (list) {
        const item = makeEl('div', 'rounded-lg border border-slate-100 px-3 py-2');
        const top = makeEl('div', 'flex items-center justify-between gap-3');
        top.append(makeEl('span', 'text-sm font-medium text-slate-700', statusLabel(row.status)));
        top.append(makeEl('span', 'text-sm font-semibold tabular-nums text-slate-950', number(row.total)));
        const track = makeEl('div', 'mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100');
        const bar = makeEl('div', 'h-full rounded-full');
        bar.style.width = `${Math.max(0, Math.min(100, Number(row.percent) || 0))}%`;
        bar.style.backgroundColor = color;
        track.appendChild(bar);
        item.append(top, track);
        list.appendChild(item);
      }
    });

    const rate = Number(kpi?.completion_rate || 0);
    const badge = document.getElementById('statusHealthBadge');
    if (badge) {
      badge.textContent = `${percent(rate)} selesai`;
      badge.className = rate >= 80
        ? 'rounded-md border border-emerald-100 bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700'
        : 'rounded-md border border-amber-100 bg-amber-50 px-2 py-1 text-xs font-medium text-amber-700';
    }
  }

  function renderRankList(id, rows, color, emptyText) {
    const box = document.getElementById(id);
    if (!box) return;
    box.replaceChildren();

    if (!rows?.length) {
      box.appendChild(makeEl('div', 'rounded-lg border border-dashed border-slate-200 bg-slate-50 px-3 py-4 text-sm text-slate-500', emptyText));
      return;
    }

    const max = Math.max(...rows.map((row) => Number(row.total) || 0), 1);
    rows.slice(0, 5).forEach((row, index) => {
      const item = makeEl('div', 'space-y-1.5');
      const top = makeEl('div', 'flex items-center justify-between gap-3');
      const label = makeEl('div', 'min-w-0 truncate text-sm font-medium text-slate-700', `${index + 1}. ${row.label}`);
      const value = makeEl('div', 'shrink-0 text-sm font-semibold tabular-nums text-slate-950', number(row.total));
      top.append(label, value);
      const track = makeEl('div', 'h-2 overflow-hidden rounded-full bg-slate-100');
      const bar = makeEl('div', 'h-full rounded-full');
      bar.style.width = `${Math.max(3, (Number(row.total) || 0) / max * 100)}%`;
      bar.style.backgroundColor = color;
      track.appendChild(bar);
      item.append(top, track);
      box.appendChild(item);
    });
  }

  function renderAging(rows, kpi) {
    const box = document.getElementById('agingList');
    if (!box) return;
    box.replaceChildren();

    if (!rows?.length) {
      box.appendChild(makeEl('div', 'rounded-lg border border-dashed border-slate-200 bg-slate-50 px-3 py-4 text-sm text-slate-500', 'Belum ada backlog aktif.'));
      return;
    }

    const colors = ['#10b981', '#2563eb', '#f59e0b', '#ef4444'];
    rows.forEach((row, index) => {
      const item = makeEl('div', 'rounded-lg border border-slate-100 px-3 py-3');
      const top = makeEl('div', 'flex items-center justify-between gap-3');
      top.append(makeEl('span', 'text-sm font-medium text-slate-700', row.label));
      top.append(makeEl('span', 'text-sm font-semibold tabular-nums text-slate-950', `${number(row.total)} tiket`));
      const track = makeEl('div', 'mt-2 h-2 overflow-hidden rounded-full bg-slate-100');
      const bar = makeEl('div', 'h-full rounded-full');
      bar.style.width = `${Math.max(0, Math.min(100, Number(row.percent) || 0))}%`;
      bar.style.backgroundColor = colors[index] || '#64748b';
      track.appendChild(bar);
      item.append(top, track);
      box.appendChild(item);
    });

    const critical = Number(kpi?.aging_over_7_days || 0);
    const badge = document.getElementById('agingCriticalBadge');
    if (badge) {
      badge.textContent = critical > 0 ? `${number(critical)} > 7 hari` : 'Backlog sehat';
      badge.className = critical > 0
        ? 'rounded-md border border-red-100 bg-red-50 px-2 py-1 text-xs font-medium text-red-700'
        : 'rounded-md border border-emerald-100 bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700';
    }
  }

  function renderInsights(json) {
    const box = document.getElementById('insightList');
    if (!box) return;
    box.replaceChildren();

    const kpi = json.kpi || {};
    const insights = [];
    const category = json.kategoriRows?.[0];
    const office = json.topRows?.[0];
    const root = json.rootRows?.[0];

    if (Number(kpi.total || 0) === 0) {
      insights.push({ title: 'Belum ada data', body: 'Filter aktif tidak menemukan tiket untuk dianalisa.', tone: 'slate' });
    } else {
      if (category) {
        insights.push({ title: 'Kategori dominan', body: `${category.label} menyumbang ${number(category.total)} tiket (${percent(category.percent)}).`, tone: 'blue' });
      }
      if (office) {
        insights.push({ title: 'Kantor tertinggi', body: `${office.label} menjadi pembuat tiket terbanyak pada periode ini.`, tone: 'cyan' });
      }
      insights.push({ title: 'Backlog aktif', body: `${number(kpi.active)} tiket masih aktif, termasuk ${number(kpi.unassigned)} tiket open belum diambil.`, tone: Number(kpi.active) > 0 ? 'amber' : 'emerald' });
      if (root) {
        insights.push({ title: 'Root cause utama', body: `${root.label} tercatat pada ${number(root.total)} tiket selesai.`, tone: 'emerald' });
      }
      insights.push({ title: 'Rasio penyelesaian', body: `${percent(kpi.completion_rate)} tiket sudah selesai dari total volume filter aktif.`, tone: Number(kpi.completion_rate) >= 80 ? 'emerald' : 'violet' });
      if (Number(kpi.aging_over_7_days || 0) > 0) {
        insights.push({ title: 'Prioritas backlog', body: `${number(kpi.aging_over_7_days)} tiket aktif berumur lebih dari 7 hari.`, tone: 'red' });
      }
    }

    const toneClass = {
      blue: 'border-blue-100 bg-blue-50 text-blue-700',
      cyan: 'border-cyan-100 bg-cyan-50 text-cyan-700',
      amber: 'border-amber-100 bg-amber-50 text-amber-700',
      emerald: 'border-emerald-100 bg-emerald-50 text-emerald-700',
      violet: 'border-violet-100 bg-violet-50 text-violet-700',
      red: 'border-red-100 bg-red-50 text-red-700',
      slate: 'border-slate-200 bg-slate-50 text-slate-700',
    };

    insights.forEach((insight) => {
      const card = makeEl('div', `rounded-lg border p-3 ${toneClass[insight.tone] || toneClass.slate}`);
      card.append(makeEl('div', 'text-sm font-semibold', insight.title));
      card.append(makeEl('p', 'mt-1 text-sm leading-5 opacity-90', insight.body));
      box.appendChild(card);
    });

    setText('insightCount', `${insights.length} insight`);
  }

  function updateDashboard(json) {
    latestStats = json;
    const kpi = json.kpi || {};
    const meta = json.meta || {};

    setText('generatedAt', `Update ${meta.generated_at || '-'}`);
    setText('periodLabel', meta.range_label || 'Semua data');
    setText('scopeLabel', meta.scope_label || 'Semua kantor');
    setText('trendCaption', meta.trend_label || '30 hari terakhir');

    setText('kpiTotal', number(kpi.total));
    setText('kpiOpen', number(kpi.active ?? kpi.open));
    setText('kpiClosed', number(kpi.closed));
    setText('kpiCompletion', percent(kpi.completion_rate));
    setText('kpiResponse', kpi.avg_response || '-');
    setText('kpiAvg', kpi.avg_resolution || '-');
    setText('kpiTotalSub', meta.range_label || 'Semua data');
    setText('kpiActiveSub', `${number(kpi.open_queue)} open, ${number(kpi.in_progress)} proses`);
    setText('kpiClosedSub', `${number(kpi.escalated)} eskalasi, ${number(kpi.vendor_resolved)} selesai vendor`);
    setText('statusCenterTotal', number(kpi.total));
    setWidth('completionBar', kpi.completion_rate);

    renderTrendChart(json);
    donutChart(json.statusRows || []);

    renderStatus(json.statusRows || [], kpi);
    renderRankList('categoryList', json.kategoriRows || [], '#2563eb', 'Belum ada data kategori.');
    renderRankList('officeList', json.topRows || [], '#0891b2', 'Belum ada data kantor.');
    renderRankList('rootList', json.rootRows || [], '#10b981', 'Belum ada data root cause.');
    renderAging(json.agingRows || [], kpi);
    renderInsights(json);
  }

  async function fetchStats(from = '', to = '') {
    const refresh = document.getElementById('btnRefresh');
    try {
      showLoading(true);
      if (refresh) refresh.disabled = true;

      const params = new URLSearchParams();
      if (from) params.set('date_from', from);
      if (to) params.set('date_to', to);
      const kodeKantor = document.getElementById('filterKodeKantor')?.value;
      if (kodeKantor) params.set('kode_kantor', kodeKantor);

      const res = await fetch(`<?php echo e(route('stats.data')); ?>?${params.toString()}`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
      });

      const json = await res.json().catch(() => ({}));
      if (!res.ok) {
        throw new Error(json.message || `HTTP ${res.status}`);
      }

      updateDashboard(json);
    } catch (err) {
      console.error(err);
      alert('Terjadi kesalahan saat memuat data statistik: ' + err.message);
    } finally {
      showLoading(false);
      if (refresh) refresh.disabled = false;
    }
  }

  function selectedRange() {
    return {
      from: document.getElementById('filterFrom')?.value || '',
      to: document.getElementById('filterTo')?.value || '',
      kodeKantor: document.getElementById('filterKodeKantor')?.value || '',
    };
  }

  document.getElementById('filterForm')?.addEventListener('submit', (event) => {
    event.preventDefault();
    const { from, to } = selectedRange();
    fetchStats(from, to);
  });

  document.getElementById('trendMode')?.addEventListener('change', () => {
    if (latestStats) {
      renderTrendChart(latestStats);
    }
  });

  document.getElementById('btnReset')?.addEventListener('click', () => {
    const from = document.getElementById('filterFrom');
    const to = document.getElementById('filterTo');
    const kantor = document.getElementById('filterKodeKantor');
    if (from) from.value = '';
    if (to) to.value = '';
    if (kantor) kantor.value = '';
    fetchStats('', '');
  });

  document.getElementById('btnDownload')?.addEventListener('click', () => {
    const { from, to, kodeKantor } = selectedRange();
    const params = new URLSearchParams();
    if (from) params.set('date_from', from);
    if (to) params.set('date_to', to);
    if (kodeKantor) params.set('kode_kantor', kodeKantor);
    window.location.href = `<?php echo e(route('it.tickets.export')); ?>?${params.toString()}`;
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
    reportModeModal?.classList.remove('hidden');
  }

  function closeReportModeModal() {
    reportModeModal?.classList.add('hidden');
  }

  function openReportSummaryModal() {
    reportSummaryModal?.classList.remove('hidden');
    reportSummaryLoading?.classList.remove('hidden');
    reportSummaryTextarea?.classList.add('hidden');
    if (reportSummaryTextarea) {
      reportSummaryTextarea.value = '';
      reportSummaryTextarea.placeholder = 'Ringkasan akan muncul di sini; Anda boleh mengubah teks sebelum mengunduh PDF.';
    }
    if (reportSummaryConfirm) reportSummaryConfirm.disabled = true;
  }

  function closeReportSummaryModal() {
    reportSummaryModal?.classList.add('hidden');
  }

  async function loadSummaryFromAI() {
    openReportSummaryModal();
    const { from, to, kodeKantor } = selectedRange();

    try {
      const res = await fetch(`<?php echo e(route('it.stats.report.summary_preview')); ?>`, {
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
          kode_kantor: kodeKantor || null,
        }),
      });
      const json = await res.json().catch(() => ({}));
      if (!res.ok) {
        throw new Error(json.message || `HTTP ${res.status}`);
      }
      if (reportSummaryTextarea) {
        reportSummaryTextarea.value = (json.executive_summary || '').trim();
        if (json.ai_unavailable && !reportSummaryTextarea.value) {
          reportSummaryTextarea.value = 'Ringkasan otomatis tidak tersedia. Silakan tulis ringkasan eksekutif di sini, lalu klik Generate PDF.';
        }
      }
    } catch (err) {
      console.error(err);
      alert('Gagal memuat ringkasan AI: ' + err.message);
      if (reportSummaryTextarea) {
        reportSummaryTextarea.value = 'Silakan tulis ringkasan eksekutif di sini, lalu klik Generate PDF.';
      }
    } finally {
      reportSummaryLoading?.classList.add('hidden');
      reportSummaryTextarea?.classList.remove('hidden');
      if (reportSummaryConfirm) reportSummaryConfirm.disabled = false;
      reportSummaryTextarea?.focus();
    }
  }

  function openManualSummary() {
    openReportSummaryModal();
    reportSummaryLoading?.classList.add('hidden');
    reportSummaryTextarea?.classList.remove('hidden');
    if (reportSummaryTextarea) {
      reportSummaryTextarea.value = '';
      reportSummaryTextarea.placeholder = 'Silakan tulis Executive Summary secara manual di sini.';
      reportSummaryTextarea.focus();
    }
    if (reportSummaryConfirm) reportSummaryConfirm.disabled = false;
  }

  document.getElementById('btnReport')?.addEventListener('click', openReportModeModal);
  document.getElementById('reportModeCancel')?.addEventListener('click', closeReportModeModal);
  document.getElementById('reportModeModalBackdrop')?.addEventListener('click', closeReportModeModal);
  document.getElementById('reportModeManual')?.addEventListener('click', () => {
    closeReportModeModal();
    openManualSummary();
  });
  document.getElementById('reportModeAI')?.addEventListener('click', async () => {
    closeReportModeModal();
    await loadSummaryFromAI();
  });

  document.getElementById('reportSummaryCancel')?.addEventListener('click', closeReportSummaryModal);
  document.getElementById('reportSummaryCloseX')?.addEventListener('click', closeReportSummaryModal);
  document.getElementById('reportSummaryModalBackdrop')?.addEventListener('click', closeReportSummaryModal);
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      closeReportModeModal();
      closeReportSummaryModal();
    }
  });

  reportSummaryConfirm?.addEventListener('click', async () => {
    const { from, to, kodeKantor } = selectedRange();
    const executiveSummary = reportSummaryTextarea?.value || '';

    reportSummaryConfirm.disabled = true;
    try {
      const fd = new FormData();
      fd.append('_token', csrfToken());
      fd.append('date_from', from);
      fd.append('date_to', to);
      fd.append('kode_kantor', kodeKantor);
      fd.append('executive_summary', executiveSummary);

      const res = await fetch(`<?php echo e(route('it.stats.report')); ?>`, {
        method: 'POST',
        body: fd,
        headers: {
          'Accept': 'application/pdf, text/html',
          'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
      });

      const contentType = (res.headers.get('content-type') || '').toLowerCase();
      const blob = await res.blob();

      if (!res.ok) {
        const text = await blob.text().catch(() => '');
        throw new Error(text.slice(0, 240) || `HTTP ${res.status}`);
      }

      if (contentType.includes('application/pdf')) {
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'laporan_tiket.pdf';
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
      } else {
        const htmlUrl = URL.createObjectURL(blob);
        window.open(htmlUrl, '_blank');
        setTimeout(() => URL.revokeObjectURL(htmlUrl), 1000);
      }
      closeReportSummaryModal();
    } catch (err) {
      console.error(err);
      alert('Gagal membuat laporan: ' + err.message);
    } finally {
      reportSummaryConfirm.disabled = false;
    }
  });

  function runInitialFetchStats() {
    const { from, to } = selectedRange();
    fetchStats(from, to);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => requestAnimationFrame(runInitialFetchStats));
  } else {
    requestAnimationFrame(runInitialFetchStats);
  }

  window.addEventListener('resize', () => {
    Object.values(charts).forEach((chart) => {
      try { chart?.resize?.(); } catch (_) {}
    });
  });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\helpdesk-app\resources\views/it/stats.blade.php ENDPATH**/ ?>