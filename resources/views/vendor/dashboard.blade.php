@extends('layouts.app')
@section('title','Tiket Eskalasi Vendor')

@section('content')
<div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
  <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between mb-4">
    <div>
      <h2 class="text-lg font-semibold text-gray-800">Tiket Eskalasi Vendor</h2>
      <p class="text-sm text-gray-500">Tiket yang berstatus <strong>ESKALASI_VENDOR</strong> dan ditujukan kepada Anda.</p>
    </div>

    <form method="GET" action="{{ route('vendor.dashboard') }}" class="grid grid-cols-2 sm:flex gap-2">
      <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nomor / deskripsi"
             class="col-span-2 sm:col-span-1 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">

      <select name="kategori" class="rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
        <option value="">Kategori</option>
        @foreach(($kategori ?? ['JARINGAN','LAYANAN','CBS','OTHER']) as $k)
          <option value="{{ $k }}" @selected(request('kategori')===$k)>{{ $k }}</option>
        @endforeach
      </select>

      <button class="rounded-lg bg-gradient-to-r from-blue-500 to-sky-500 text-white px-3 py-2">Filter</button>

      @if(request()->hasAny(['q','kategori']))
        <a href="{{ route('vendor.dashboard') }}" class="text-sm px-2 py-2 text-gray-600 hover:underline">Reset</a>
      @endif
    </form>
  </div>

  <div class="hidden md:block overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-gray-50 text-gray-600">
        <tr>
          <th class="py-3 px-4 text-left">#</th>
          <th class="py-3 px-4 text-left">Nomor</th>
          <th class="py-3 px-4 text-left">Kategori</th>
          <th class="py-3 px-4 text-left">Status</th>
          <th class="py-3 px-4 text-left">Dibuat</th>
          <th class="py-3 px-4 text-left">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        @forelse($tickets as $i => $t)
          <tr class="hover:bg-gray-50">
            <td class="py-3 px-4 text-gray-500">{{ $tickets->firstItem() + $i }}</td>
            <td class="py-3 px-4 font-medium">
              <a href="{{ route('ticket.show', $t->id) }}" class="text-indigo-600 hover:underline">{{ $t->nomor_tiket }}</a>
            </td>
            <td class="py-3 px-4">{{ $t->kategori }}</td>
            <td class="py-3 px-4">
              @php
                $badge = match($t->status){
                  'OPEN'             => 'bg-red-100 text-gray-700 ring-gray-200',
                  'ON_PROGRESS'      => 'bg-amber-100 text-amber-800 ring-amber-200',
                  'ESKALASI_VENDOR'  => 'bg-fuchsia-100 text-fuchsia-800 ring-fuchsia-200',
                  'VENDOR_RESOLVED'  => 'bg-indigo-100 text-indigo-800 ring-indigo-200',
                  'CLOSED'           => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
                  default            => 'bg-gray-100 text-gray-700 ring-gray-200',
                };
              @endphp
              <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 {{ $badge }}">{{ $t->status }}</span>
            </td>
            <td class="py-3 px-4 whitespace-nowrap">{{ $t->created_at->format('d M Y H:i') }}</td>
            <td class="py-3 px-4">
              <a href="{{ route('ticket.show', $t->id) }}" class="inline-flex items-center rounded-lg bg-gradient-to-r from-blue-500 to-sky-500 px-3 py-1.5 text-white hover:bg-gray-800">Detail</a>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="py-6 px-4 text-center text-gray-500">Tidak ada tiket.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $tickets->links() }}</div>
</div>
@endsection
