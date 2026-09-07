<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Rekap Status Tiket</title>
  <style>
    @page { size: A4 landscape; margin: 14mm; }
    body { margin: 0; color: #172033; font-family: Arial, Helvetica, sans-serif; font-size: 12px; }
    .header { border-bottom: 2px solid #0891b2; padding-bottom: 10px; margin-bottom: 18px; }
    h1 { margin: 0 0 6px; font-size: 20px; }
    .meta { color: #596579; font-size: 11px; line-height: 1.6; }
    .total { margin-bottom: 16px; padding: 14px; background: #ecfeff; border: 1px solid #a5f3fc; }
    .total-label { color: #47606a; font-size: 11px; }
    .total-value { margin-top: 3px; color: #0e7490; font-size: 26px; font-weight: bold; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #d8dee8; padding: 9px; }
    th { background: #f1f5f9; text-align: left; }
    .number { text-align: right; }
    tfoot td { background: #f8fafc; font-weight: bold; }
    .footer { margin-top: 18px; color: #778195; font-size: 10px; }
    .office-title { margin: 22px 0 8px; font-size: 14px; font-weight: bold; }
    .office-table { table-layout: fixed; }
    .office-table th, .office-table td { padding: 6px; font-size: 8px; line-height: 1.35; vertical-align: top; overflow-wrap: anywhere; }
    .office-table tr { page-break-inside: avoid; }
  </style>
</head>
<body>
  <div class="header">
    <h1>Rekap Status Tiket</h1>
    <div class="meta">
      Periode: <?php echo e($dateFrom ?: 'Semua'); ?> &mdash; <?php echo e($dateTo ?: 'Semua'); ?><br>
      Kantor pembuat: <?php echo e($officeLabel); ?><br>
      Dibuat: <?php echo e(now()->format('d M Y H:i')); ?>

    </div>
  </div>

  <div class="total">
    <div class="total-label">Total tiket sesuai filter</div>
    <div class="total-value"><?php echo e(number_format($total, 0, ',', '.')); ?></div>
  </div>

  <table>
    <thead>
      <tr>
        <th>Status</th>
        <th class="number">Jumlah Tiket</th>
        <th class="number">Persentase</th>
      </tr>
    </thead>
    <tbody>
      <?php $__currentLoopData = $statusRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
          <td><?php echo e($row['label']); ?></td>
          <td class="number"><?php echo e(number_format($row['total'], 0, ',', '.')); ?></td>
          <td class="number"><?php echo e(number_format($row['percentage'], 2, ',', '.')); ?>%</td>
        </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
    <tfoot>
      <tr>
        <td>Total</td>
        <td class="number"><?php echo e(number_format($total, 0, ',', '.')); ?></td>
        <td class="number"><?php echo e($total > 0 ? '100,00%' : '0,00%'); ?></td>
      </tr>
    </tfoot>
  </table>

  <div class="office-title">Rekap Per Cabang</div>
  <table class="office-table">
    <thead>
      <tr>
        <th style="width:17%;">Cabang</th>
        <th class="number" style="width:8%;">Total Tiket</th>
        <th style="width:23%;">Rekap Root Cause</th>
        <th style="width:22%;">Rekap Kategori</th>
        <th style="width:30%;">Rekap Subkategori</th>
      </tr>
    </thead>
    <tbody>
      <?php $__empty_1 = true; $__currentLoopData = $officeRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $office): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr>
          <td><?php echo e($office['office']); ?></td>
          <td class="number"><?php echo e(number_format($office['total'], 0, ',', '.')); ?></td>
          <td><?php echo e($office['roots'] ?: 'Tidak tersedia'); ?></td>
          <td><?php echo e($office['categories'] ?: 'Tidak tersedia'); ?></td>
          <td><?php echo e($office['subcategories'] ?: 'Tidak tersedia'); ?></td>
        </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr>
          <td colspan="5" style="text-align:center;">Tidak ada data pada periode dan filter yang dipilih.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <div class="footer">Dokumen ini hanya berisi rekapitulasi status dan tidak memuat detail isi tiket.</div>
</body>
</html>
<?php /**PATH C:\laragon\www\helpdesk-app\resources\views/it/stats_recap_pdf.blade.php ENDPATH**/ ?>