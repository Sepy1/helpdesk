@extends('layouts.app')
@section('title','Tiket Saya (IT)')

@section('content')
<div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-5">
  <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div>
      <h2 class="text-lg font-semibold text-gray-800">Tiket yang Saya Tangani</h2>
      <p class="text-sm text-gray-500">Hanya tiket dengan IT handler = {{ auth()->user()->name }}.</p>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('it.my') }}" class="grid grid-cols-2 sm:flex gap-2">
      <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nomor / deskripsi"
             class="rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
      <select name="kategori" class="rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
        <option value="">Kategori</option>
        @foreach(\App\Http\Controllers\TicketController::KATEGORI as $k)
          <option value="{{ $k }}" @selected(request('kategori')===$k)>{{ $k }}</option>
        @endforeach
      </select>
      <select name="status" class="rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
        <option value="">Status</option>
        @foreach(\App\Http\Controllers\TicketController::STATUS as $s)
          <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
        @endforeach
      </select>
      <button class="rounded-lg bg-gray-900 text-white px-3 py-2">Filter</button>
      @if(request()->hasAny(['q','kategori','status']))
        <a href="{{ route('it.my') }}" class="text-sm px-2 py-2 text-gray-600 hover:underline">Reset</a>
      @endif
    </form>
  </div>
</div>

{{-- Mobile: Cards --}}
<div class="mt-4 grid gap-3 md:hidden">
  @forelse($tickets as $t)
    @include('components.ticket-card', ['ticket' => $t, 'showActions' => true])
  @empty
    <div class="rounded-xl border border-dashed p-6 text-center text-gray-500">Belum ada tiket yang Anda tangani.</div>
  @endforelse
</div>

{{-- Desktop: Table --}}
<div class="mt-4 hidden md:block overflow-x-auto rounded-xl border border-gray-100 bg-white">
  <table class="min-w-full text-sm">
    <thead class="bg-gray-50 text-gray-600">
      <tr>
        <th class="py-3 px-4 text-left">#</th>
        <th class="py-3 px-4 text-left">Nomor</th>
        <th class="py-3 px-4 text-left">Kategori</th>
        <th class="py-3 px-4 text-left">Status</th>
        <th class="py-3 px-4 text-left">Pemohon</th>
        <th class="py-3 px-4 text-left">Dibuat</th>
        <th class="py-3 px-4 text-left">Aksi</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
      @forelse($tickets as $i => $t)
        @php
          $badge = match($t->status){
            'OPEN' => 'bg-gray-100 text-gray-700 ring-gray-200',
            'ON_PROGRESS' => 'bg-amber-100 text-amber-800 ring-amber-200',
            'CLOSED' => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
          };
        @endphp
        <tr class="hover:bg-gray-50">
          <td class="py-3 px-4 text-gray-500">{{ $tickets->firstItem() + $i }}</td>
          <td class="py-3 px-4 font-medium">
            <a href="{{ route('ticket.show', $t->id) }}" class="text-indigo-600 hover:underline">
              {{ $t->nomor_tiket }}
            </a>
          </td>
          <td class="py-3 px-4">{{ $t->kategori }}</td>
          <td class="py-3 px-4">
            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 {{ $badge }}">
              {{ $t->status }}
            </span>
          </td>
          <td class="py-3 px-4">{{ $t->user->name ?? '-' }}</td>
          <td class="py-3 px-4">{{ $t->created_at->format('d M Y H:i') }}</td>
          <td class="py-3 px-4">
            <div class="flex flex-wrap gap-2">
              <a href="{{ route('ticket.show', $t->id) }}"
                 class="inline-flex items-center rounded-lg bg-gray-900 px-3 py-1.5 text-white hover:bg-gray-800">
                Detail
              </a>

              @if($t->status === 'ON_PROGRESS' && $t->it_id === auth()->id())
                <form method="POST" action="{{ route('it.ticket.release', $t->id) }}">
                  @csrf
                  <button class="rounded-lg bg-gray-200 px-3 py-1.5 text-gray-800 hover:bg-gray-300">
                    Lepas
                  </button>
                </form>
                <form method="POST" action="{{ route('it.ticket.close', $t->id) }}">
                  @csrf
                  <button class="rounded-lg bg-emerald-600 px-3 py-1.5 text-white hover:bg-emerald-700">
                    Tutup
                  </button>
                </form>
              @elseif($t->status === 'CLOSED')
                <form method="POST" action="{{ route('it.ticket.reopen', $t->id) }}">
                  @csrf
                  <button class="rounded-lg bg-amber-600 px-3 py-1.5 text-white hover:bg-amber-700">
                    Reopen
                  </button>
                </form>
              @endif
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="7" class="py-6 px-4 text-center text-gray-500">Belum ada tiket yang Anda tangani.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="mt-4">
  {{ $tickets->links() }}
</div>
@endsection
