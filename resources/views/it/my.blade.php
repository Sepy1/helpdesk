@extends('layouts.app')
@section('title','Tiket Saya (Diambil)')

@section('content')
<div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
  <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between mb-4">
    <div>
      <h2 class="text-lg font-semibold text-gray-800">Tiket Saya (Sedang Ditangani)</h2>
      <p class="text-sm text-gray-500">Daftar tiket dengan handler: {{ auth()->user()->name }}</p>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('it.my') }}" class="grid grid-cols-2 sm:flex gap-2">
      <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nomor / deskripsi"
             class="rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
      <select name="kategori" class="rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
        <option value="">Kategori</option>
        @foreach(['JARINGAN','LAYANAN','CBS','OTHER'] as $k)
          <option value="{{ $k }}" @selected(request('kategori')===$k)>{{ $k }}</option>
        @endforeach
      </select>
      <select name="status" class="rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
        <option value="">Status</option>
        @foreach(['OPEN','ON_PROGRESS','CLOSED'] as $s)
          <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
        @endforeach
      </select>
      <button class="rounded-lg bg-gray-900 text-white px-3 py-2">Filter</button>
    </form>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="text-left bg-gray-50 text-gray-600">
        <tr>
          <th class="py-3 px-4">#</th>
          <th class="py-3 px-4">Nomor</th>
          <th class="py-3 px-4">Kategori</th>
          <th class="py-3 px-4">Pembuat</th>
          <th class="py-3 px-4">Status</th>
          <th class="py-3 px-4">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        @forelse($tickets as $i => $t)
          <tr class="hover:bg-gray-50">
            <td class="py-3 px-4 text-gray-500">{{ $tickets->firstItem() + $i }}</td>
            <td class="py-3 px-4 font-medium">
              <a class="text-indigo-600 hover:underline" href="{{ route('ticket.show', $t->id) }}">
                {{ $t->nomor_tiket }}
              </a>
            </td>
            <td class="py-3 px-4">{{ $t->kategori }}</td>
            <td class="py-3 px-4">{{ $t->user->name ?? '-' }}</td>
            <td class="py-3 px-4">
              @php
                $badge = match($t->status) {
                  'OPEN' => 'bg-gray-100 text-gray-700 ring-gray-200',
                  'ON_PROGRESS' => 'bg-amber-100 text-amber-800 ring-amber-200',
                  'CLOSED' => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
                };
              @endphp
              <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 {{ $badge }}">
                {{ $t->status }}
              </span>
            </td>
            <td class="py-3 px-4">
              {{-- Karena ini list milik IT sendiri, tampilkan Release/Tutup cepat --}}
              @if($t->status === 'ON_PROGRESS')
                <form class="inline" method="POST" action="{{ route('it.ticket.release',$t->id) }}">
                  @csrf
                  <button class="rounded-lg bg-gray-200 px-3 py-1.5 text-gray-700 hover:bg-gray-300">Lepas</button>
                </form>
                <form class="inline" method="POST" action="{{ route('it.ticket.close',$t->id) }}">
                  @csrf
                  <button class="rounded-lg bg-emerald-600 px-3 py-1.5 text-white hover:bg-emerald-700">Tutup</button>
                </form>
              @else
                <a class="rounded-lg bg-gray-100 px-3 py-1.5 text-gray-500" href="{{ route('ticket.show',$t->id) }}">
                  Lihat
                </a>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="py-6 px-4 text-center text-gray-500">Belum ada tiket yang kamu tangani.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $tickets->links() }}</div>
</div>
@endsection
