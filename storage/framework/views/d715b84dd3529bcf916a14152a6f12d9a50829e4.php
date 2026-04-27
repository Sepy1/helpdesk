
<?php $__env->startSection('title','Statistik Tiket'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
  
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
          <?php $__currentLoopData = ($users ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($u->id); ?>"><?php echo e($u->name); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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

  
  <div id="loadingOverlay" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 hidden">
    <div class="bg-white rounded-lg p-4 flex items-center gap-3 shadow-lg">
      <svg class="animate-spin w-6 h-6 text-sky-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
      <div>
        <div class="text-sm font-medium text-gray-800">Memuat statistik…</div>
        <div class="text-xs text-gray-500">Tunggu sebentar — ini normal jika data banyak.</div>
      </div>
    </div>
  </div>

  
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    
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
      const user = document.getElementById('filterUser')?.value;
      if (user) params.set('user_id', user);
      const url = `<?php echo e(route('stats.data')); ?>?${params.toString()}`;
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
    const user = document.getElementById('filterUser')?.value;
    if (user) params.set('user_id', user);
    const url = `<?php echo e(route('it.tickets.export')); ?>?${params.toString()}`;
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

      const res = await fetch(`<?php echo e(route('it.stats.report')); ?>`, {
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\helpdesk-app\resources\views/it/stats.blade.php ENDPATH**/ ?>