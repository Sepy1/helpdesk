<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Laporan Statistik Tiket</title>

  <style>
    /* Ensure PDF uses A4 and consistent margins */
    @page { size: A4; margin: 15mm; }

    body {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 12px;
      color: #111;
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    .header {
      margin-bottom: 15px;
    }

    .h1 {
      font-size: 18px;
      font-weight: bold;
    }

    .period {
      font-size: 11px;
      color: #555;
    }

    .muted {
      color: #666;
      font-size: 10px;
    }

    .card {
      background: #f6f7fb;
      border-radius: 10px;
      padding: 12px;
      height: 100%;
    }

    .card-title {
      font-weight: bold;
      margin-bottom: 8px;
      font-size: 13px;
    }

    .kpi {
      text-align: center;
    }

    .kpi .num {
      font-size: 26px;
      font-weight: bold;
      margin-top: 4px;
    }

    /* Chart image sizing for PDF */
    .kpi-chart {
      width: 160px;
      height: auto;
      display: block;
      margin: 0 auto;
    }

    .table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }

    .table th, .table td {
      border: 1px solid #ddd;
      padding: 6px;
      font-size: 11px;
    }

    .table th {
      background: #f1f3f7;
    }

    /* Avoid row breaks inside a single ticket row */
    .table tr {
      page-break-inside: avoid;
    }

    /* Smaller text for dense tables when printing */
    @media print {
      body { font-size: 11px; }
      .table th, .table td { padding: 5px; font-size: 10px; }
    }

    .chart-block {
      page-break-inside: avoid;
      break-inside: avoid;
      margin-top: 8px;
      margin-bottom: 12px;
    }

    .chart-frame {
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      padding: 6px;
      text-align: center;
      min-height: 170px;
      max-height: 170px;
      overflow: hidden;
    }

    .chart-frame img {
      width: 100%;
      max-width: 620px;
      height: 150px;
      object-fit: contain;
    }

    .chart-row {
      width: 100%;
      border-collapse: collapse;
      page-break-inside: avoid;
      break-inside: avoid;
      margin-bottom: 12px;
    }

    .chart-row td {
      width: 50%;
      vertical-align: top;
      padding: 0 6px;
    }

    .page-break-before {
      page-break-before: always;
      break-before: page;
    }

  </style>
</head>

<body>

  <!-- HEADER -->
  <div class="header" style="border-bottom:1px solid #e6e9ef; padding-bottom:8px; margin-bottom:12px;">
    <div style="display:flex; justify-content:space-between; align-items:baseline;">
      <div style="display:flex; align-items:baseline; gap:12px; white-space:nowrap;">
        <div class="h1" style="margin:0; line-height:1;">Laporan Tiket Sambatan</div>
        <div class="period" style="white-space:nowrap; margin:0; line-height:1; font-size:12px; color:#555;">Periode: <?php echo e($dateFrom ?? 'Semua'); ?> — <?php echo e($dateTo ?? 'Semua'); ?></div>
      </div>
      <div style="text-align:right; font-size:11px; color:#666;">Generated: <?php echo e(now()->format('d M Y H:i')); ?></div>
    </div>
  </div>

  <!-- TOP SECTION REMOVED (KPI / DONUT / ROOT CAUSE) -->

  <!-- TABLE DATA -->

  <!-- SUMMARY & ROOT CAUSE TABLES (plain tables, no cards/images) -->
  <table style="width:100%; margin-bottom:12px;">
    <tr>
      <td style="width:40%; vertical-align:top; padding-right:8px;">
        <div style="margin-bottom:6px; font-weight:bold;">Statistik Tiket</div>
        <table class="table">
          <tbody>
            <tr>
              <td style="padding:6px; color:#444;">Total Tiket</td>
              <td style="padding:6px; text-align:right; font-weight:bold;"><?php echo e($kpi['total'] ?? '-'); ?></td>
            </tr>
            <tr>
              <td style="padding:6px; color:#444;">Tiket Closed</td>
              <td style="padding:6px; text-align:right; font-weight:bold;"><?php echo e($kpi['closed'] ?? '-'); ?></td>
            </tr>
            <tr>
              <td style="padding:6px; color:#444;">Tiket On Progress</td>
              <td style="padding:6px; text-align:right; font-weight:bold;"><?php echo e($kpi['on_progress'] ?? '-'); ?></td>
            </tr>
            <tr>
              <td style="padding:6px; color:#444;">Tiket dengan history Eskalasi Vendor</td>
              <td style="padding:6px; text-align:right; font-weight:bold;"><?php echo e($kpi['eskalasi'] ?? '-'); ?></td>
            </tr>
          </tbody>
        </table>
      </td>

      <td style="width:60%; vertical-align:top; padding-left:8px;">
        <div style="margin-bottom:6px; font-weight:bold;">Jumlah Tiket Berdasar Root Cause</div>
        <?php if(count($root) === 0): ?>
          <div style="text-align:center;">Tidak ada data</div>
        <?php else: ?>
          <table class="table">
            <tbody>
              <?php $__currentLoopData = $root; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <tr>
                <td style="padding:6px; font-size:11px;"><?php echo e($r->root_cause ?? 'Tidak Ditentukan'); ?></td>
                <td style="padding:6px; font-size:11px; text-align:right;"><?php echo e($r->total); ?></td>
              </tr>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
          </table>
        <?php endif; ?>
      </td>
    </tr>
  </table>

  <table class="chart-row">
    <tr>
      <td>
        <div style="font-weight:bold; margin-bottom:8px;">Perbandingan Tiket Kategori (12 Bulan)</div>
        <?php if(!empty($categoryChartUrl)): ?>
          <div class="chart-frame">
            <img src="<?php echo e($categoryChartUrl); ?>" alt="Grafik jumlah tiket per kategori">
          </div>
        <?php else: ?>
          <div class="muted">Data grafik kategori tidak tersedia.</div>
        <?php endif; ?>
      </td>
      <td>
        <div style="font-weight:bold; margin-bottom:8px;">Perbandingan Tiket Sub Kategori (12 Bulan)</div>
        <?php if(!empty($subcategoryTrendChartUrl)): ?>
          <div class="chart-frame">
            <img src="<?php echo e($subcategoryTrendChartUrl); ?>" alt="Grafik tren sub kategori 12 bulan">
          </div>
        <?php else: ?>
          <div class="muted">Data grafik sub kategori tidak tersedia.</div>
        <?php endif; ?>
      </td>
    </tr>
  </table>

  <table class="chart-row">
    <tr>
      <td>
        <div style="font-weight:bold; margin-bottom:8px;">Perbandingan Root Cause (12 Bulan)</div>
        <?php if(!empty($rootCauseTrendChartUrl)): ?>
          <div class="chart-frame">
            <img src="<?php echo e($rootCauseTrendChartUrl); ?>" alt="Grafik tren root cause 12 bulan">
          </div>
        <?php else: ?>
          <div class="muted">Data grafik root cause tidak tersedia.</div>
        <?php endif; ?>
      </td>
      <td>
        <div style="font-weight:bold; margin-bottom:8px;">Kantor Pembuat Terbanyak (12 Bulan)</div>
        <?php if(!empty($reporterTrendChartUrl)): ?>
          <div class="chart-frame">
            <img src="<?php echo e($reporterTrendChartUrl); ?>" alt="Grafik tren kantor pembuat 12 bulan">
          </div>
        <?php else: ?>
          <div class="muted">Data grafik kantor pembuat tidak tersedia.</div>
        <?php endif; ?>
      </td>
    </tr>
  </table>

  <div style="margin-top:8px; margin-bottom:12px;">
    <div style="font-weight:bold; margin-bottom:6px;">Executive Summary</div>
    <div style="border:1px solid #ddd; background:#fafafa; padding:10px; border-radius:6px; white-space:pre-line; line-height:1.45;">
      <?php echo e($executiveSummary ?? 'Ringkasan AI belum tersedia.'); ?>

    </div>
  </div>

  <div class="page-break-before"></div>
  <div style="font-weight:bold; margin-bottom:8px;">Daftar Tiket</div>
  <?php if(!empty($groupedTickets) && count($groupedTickets) > 0): ?>
    <?php $__currentLoopData = $groupedTickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $groupLabel => $groupTickets): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <?php if(!$loop->first): ?>
        <div class="page-break-before"></div>
      <?php endif; ?>
      <?php
        $groupTotal = count($groupTickets);
        $groupClosed = $groupTickets->where('status', 'CLOSED')->count();
        $groupOnProgress = $groupTickets->where('status', 'ON_PROGRESS')->count();
        $groupEskalasi = $groupTickets->where('status', 'ESKALASI_VENDOR')->count();
        $groupOpen = max(0, $groupTotal - $groupClosed);
        $groupRootCauseStats = $groupTickets
          ->groupBy(fn($t) => $t->root_cause ?? 'Tidak Ditentukan')
          ->map(fn($items) => count($items))
          ->sortDesc();
      ?>

      <div style="margin-top:10px; margin-bottom:4px; font-weight:bold;">
        <?php echo e($groupLabel); ?>

        <span class="muted">(<?php echo e(count($groupTickets)); ?> tiket)</span>
      </div>

      <table style="width:100%; border-collapse:collapse; margin-top:4px; margin-bottom:8px;">
        <tr>
          <td style="width:50%; vertical-align:top; padding-right:6px;">
            <table class="table" style="margin-top:0;">
              <tbody>
                <tr>
                  <td style="padding:6px; color:#444; width:65%;">Total Tiket</td>
                  <td style="padding:6px; text-align:right; font-weight:bold;"><?php echo e($groupTotal); ?></td>
                </tr>
                <tr>
                  <td style="padding:6px; color:#444;">Tiket Closed</td>
                  <td style="padding:6px; text-align:right; font-weight:bold;"><?php echo e($groupClosed); ?></td>
                </tr>
                <tr>
                  <td style="padding:6px; color:#444;">Tiket On Progress</td>
                  <td style="padding:6px; text-align:right; font-weight:bold;"><?php echo e($groupOnProgress); ?></td>
                </tr>
                <tr>
                  <td style="padding:6px; color:#444;">Tiket Open</td>
                  <td style="padding:6px; text-align:right; font-weight:bold;"><?php echo e($groupOpen); ?></td>
                </tr>
                <tr>
                  <td style="padding:6px; color:#444;">Tiket Eskalasi Vendor</td>
                  <td style="padding:6px; text-align:right; font-weight:bold;"><?php echo e($groupEskalasi); ?></td>
                </tr>
              </tbody>
            </table>
          </td>
          <td style="width:50%; vertical-align:top; padding-left:6px;">
            <table class="table" style="margin-top:0;">
              <thead>
                <tr>
                  <th>Root Cause</th>
                  <th style="text-align:right;">Jumlah</th>
                </tr>
              </thead>
              <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $groupRootCauseStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rootCause => $rootTotal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                  <tr>
                    <td style="padding:6px;"><?php echo e($rootCause); ?></td>
                    <td style="padding:6px; text-align:right; font-weight:bold;"><?php echo e($rootTotal); ?></td>
                  </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                  <tr>
                    <td colspan="2" style="padding:6px; text-align:center; color:#666;">Tidak ada data root cause</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </td>
        </tr>
      </table>

      <table class="table" style="margin-top:4px; margin-bottom:12px;">
        <thead>
          <tr>
            <th>#</th>
            <th>Dibuat</th>
            <th>Nomor</th>
            <th>Kategori</th>
            <th>Sub Kategori</th>
            <th>Root Cause</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php $__currentLoopData = $groupTickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <tr>
            <td style="width:5%;"><?php echo e($i+1); ?></td>
            <td style="width:13%;"><?php echo e(optional($t->created_at)->format('d M Y') ?? '-'); ?></td>
            <td style="width:14%;"><a href="<?php echo e(route('ticket.show', $t->id)); ?>" style="color:#1a73e8; text-decoration:underline;"><?php echo e($t->nomor_tiket); ?></a></td>
            <td style="width:14%;"><?php echo e($t->kategori); ?></td>
            <td style="width:14%;"><?php echo e(optional($t->subcategory)->name ?? '-'); ?></td>
            <td style="width:24%;"><?php echo e($t->root_cause ?? '-'); ?></td>
            <td style="width:16%;"><?php echo e($t->status); ?></td>
          </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
      </table>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  <?php else: ?>
    <div class="muted">Tidak ada data tiket pada periode ini.</div>
  <?php endif; ?>


</body>
</html><?php /**PATH C:\laragon\www\helpdesk-app\resources\views/it/stats_pdf.blade.php ENDPATH**/ ?>