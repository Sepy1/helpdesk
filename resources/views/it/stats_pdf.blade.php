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

  </style>
</head>

<body>

  <!-- HEADER -->
  <div class="header" style="border-bottom:1px solid #e6e9ef; padding-bottom:8px; margin-bottom:12px;">
    <div style="display:flex; justify-content:space-between; align-items:baseline;">
      <div style="display:flex; align-items:baseline; gap:12px; white-space:nowrap;">
        <div class="h1" style="margin:0; line-height:1;">Laporan Tiket Sambatan</div>
        <div class="period" style="white-space:nowrap; margin:0; line-height:1; font-size:12px; color:#555;">Periode: {{ $dateFrom ?? 'Semua' }} — {{ $dateTo ?? 'Semua' }}</div>
      </div>
      <div style="text-align:right; font-size:11px; color:#666;">Generated: {{ now()->format('d M Y H:i') }}</div>
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
              <td style="padding:6px; text-align:right; font-weight:bold;">{{ $kpi['total'] ?? '-' }}</td>
            </tr>
            <tr>
              <td style="padding:6px; color:#444;">Tiket Closed</td>
              <td style="padding:6px; text-align:right; font-weight:bold;">{{ $kpi['closed'] ?? '-' }}</td>
            </tr>
            <tr>
              <td style="padding:6px; color:#444;">Tiket On Progress</td>
              <td style="padding:6px; text-align:right; font-weight:bold;">{{ $kpi['on_progress'] ?? '-' }}</td>
            </tr>
            <tr>
              <td style="padding:6px; color:#444;">Tiket dengan history Eskalasi Vendor</td>
              <td style="padding:6px; text-align:right; font-weight:bold;">{{ $kpi['eskalasi'] ?? '-' }}</td>
            </tr>
          </tbody>
        </table>
      </td>

      <td style="width:60%; vertical-align:top; padding-left:8px;">
        <div style="margin-bottom:6px; font-weight:bold;">Jumlah Tiket Berdasar Root Cause</div>
        @if(count($root) === 0)
          <div style="text-align:center;">Tidak ada data</div>
        @else
          <table class="table">
            <tbody>
              @foreach($root as $r)
              <tr>
                <td style="padding:6px; font-size:11px;">{{ $r->root_cause ?? 'Tidak Ditentukan' }}</td>
                <td style="padding:6px; font-size:11px; text-align:right;">{{ $r->total }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        @endif
      </td>
    </tr>
  </table>
  
  @if(!empty($eskalasiTickets) && count($eskalasiTickets) > 0)
    <div style="font-weight:bold; margin-top:12px; margin-bottom:8px;">Tiket Eskalasi Vendor</div>
          <table class="table">
      <thead>
        <tr>
          <th>#</th>
          <th>Dibuat</th>
                <th>Nomor</th>
          <th>Kategori</th>
          <th>Root Cause</th>
          <th>Status</th>
          <th>Pembuat</th>
          <th>IT Handler</th>
        </tr>
      </thead>
      <tbody>
        @foreach($eskalasiTickets as $i => $t)
        <tr>
          <td style="width:4%;">{{ $i+1 }}</td>
          <td style="width:11%;">{{ optional($t->created_at)->format('d M Y') ?? '-' }}</td>
          <td style="width:12%;"><a href="{{ route('ticket.show', $t->id) }}" style="color:#1a73e8; text-decoration:underline;">{{ $t->nomor_tiket }}</a></td>
          <td style="width:15%;">{{ $t->kategori }}</td>
          <td style="width:24%;">{{ $t->root_cause ?? '-' }}</td>
          <td style="width:10%;">{{ $t->status }}</td>
          <td style="width:12%;">{{ optional($t->user)->name ?? '-' }}</td>
          <td style="width:12%;">{{ optional($t->it)->name ?? '-' }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  @endif
  <br></br>
  <div style="font-weight:bold; margin-bottom:8px;">Daftar Tiket</div>

  <table class="table">
    <thead>
      <tr>
        <th>#</th>
        <th>Dibuat</th>
        <th>Nomor</th>
        <th>Kategori</th>
        <th>Root Cause</th>
        <th>Status</th>
        <th>Pembuat</th>
        <th>IT Handler</th>
      </tr>
    </thead>

    <tbody>
      @foreach($tickets as $i => $t)
      <tr>
        <td style="width:4%;">{{ $i+1 }}</td>
        <td style="width:11%;">{{ optional($t->created_at)->format('d M Y') ?? '-' }}</td>
        <td style="width:12%;"><a href="{{ route('ticket.show', $t->id) }}" style="color:#1a73e8; text-decoration:underline;">{{ $t->nomor_tiket }}</a></td>
        <td style="width:15%;">{{ $t->kategori }}</td>
        <td style="width:24%;">{{ $t->root_cause ?? '-' }}</td>
        <td style="width:10%;">{{ $t->status }}</td>
        <td style="width:12%;">{{ optional($t->user)->name ?? '-' }}</td>
        <td style="width:12%;">{{ optional($t->it)->name ?? '-' }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>


</body>
</html>