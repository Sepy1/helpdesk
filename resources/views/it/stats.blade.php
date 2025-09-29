@extends('layouts.app')
@section('title','Statistik Tiket')

@section('content')
<div class="grid gap-6 lg:grid-cols-3">
  {{-- Kategori (bar) --}}
  <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6 lg:col-span-2">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Kategori Tiket Terbanyak</h2>
    <canvas id="chartKategori" height="120"></canvas>
  </div>

  {{-- Status (pie) --}}
  <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Status Tiket</h2>
    <canvas id="chartStatus" height="120"></canvas>
  </div>

  {{-- Top 5 User --}}
  <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6 lg:col-span-3">
    <div class="flex items-center justify-between mb-2">
      <h2 class="text-lg font-semibold text-gray-800">Top 5 User Pembuat Tiket</h2>
    </div>
    <canvas id="chartTopUser" height="140"></canvas>
  </div>
</div>

{{-- Chart.js + plugin datalabels --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

<script>
  // register plugin
  Chart.register(ChartDataLabels);

  // data dari PHP -> JS
  const kategoriLabels = @json($kategoriLabels);
  const kategoriData   = @json($kategoriData);
  const statusLabels   = @json($statusLabels);
  const statusData     = @json($statusData);
  const topLabels      = @json($topLabels);
  const topData        = @json($topData);

  // gaya angka umum
  const dl = {
    color: '#111827', // gray-900
    anchor: 'end',
    align: 'top',
    offset: 2,
    font: { weight: '600', size: 10 },
    formatter: v => v
  };

  // Bar: kategori (diperkecil & tampilkan angka)
  new Chart(document.getElementById('chartKategori'), {
    type: 'bar',
    data: {
      labels: kategoriLabels,
      datasets: [{
        label: 'Jumlah Tiket',
        data: kategoriData,
        borderWidth: 0,
        borderRadius: 4,
        maxBarThickness: 22,   // <<< kecilkan bar
        barPercentage: 0.6,
        categoryPercentage: 0.6
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
        datalabels: dl
      },
      scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
    }
  });

  // Pie: status (angka di setiap slice)
  new Chart(document.getElementById('chartStatus'), {
    type: 'pie',
    data: { labels: statusLabels, datasets: [{ data: statusData }] },
    options: {
      responsive: true,
      plugins: {
        datalabels: {
          color: '#ffffff',
          font: { weight: '700', size: 10 },
          formatter: v => v > 0 ? v : ''
        }
      }
    }
  });

  // Horizontal bar: top 5 user (diperkecil & angka di ujung)
  new Chart(document.getElementById('chartTopUser'), {
    type: 'bar',
    data: {
      labels: topLabels,
      datasets: [{
        label: 'Jumlah Tiket',
        data: topData,
        borderWidth: 0,
        borderRadius: 4,
        maxBarThickness: 20,   // <<< kecilkan bar
        barPercentage: 0.55,
        categoryPercentage: 0.6
      }]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      plugins: {
        legend: { display: false },
        datalabels: { ...dl, align: 'right', anchor: 'end' }
      },
      scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
    }
  });
</script>
@endsection
