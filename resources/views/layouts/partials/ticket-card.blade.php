@props([
  'ticket',
  'showActions' => true,   // tampilkan tombol Ambil/Lepas/Tutup
])

@php
  $badge = match($ticket->status){
    'OPEN'             => 'bg-gray-100 text-gray-700 ring-gray-200',
    'ON_PROGRESS'      => 'bg-amber-100 text-amber-800 ring-amber-200',
    'ESKALASI_VENDOR'  => 'bg-fuchsia-100 text-fuchsia-800 ring-fuchsia-200',
    'VENDOR_RESOLVED'  => 'bg-indigo-100 text-indigo-800 ring-indigo-200',
    'CLOSED'           => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
    default            => 'bg-gray-100 text-gray-700 ring-gray-200',
  };
@endphp

<div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
  <div class="flex items-start justify-between gap-3">
    <div>
      <a href="{{ route('ticket.show', $ticket->id) }}"
         class="text-sm font-semibold text-indigo-600 hover:underline">
        #{{ $ticket->nomor_tiket }}
      </a>
      <div class="mt-0.5 text-xs text-gray-500">
        Oleh <span class="font-medium text-gray-700">{{ $ticket->user->name ?? '-' }}</span>
        • {{ $ticket->created_at->format('d M Y H:i') }}
      </div>
    </div>
    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-medium ring-1 {{ $badge }}">
      {{ $ticket->status }}
    </span>
  </div>

  <div class="mt-3 grid grid-cols-2 gap-3 text-xs">
    <div>
      <div class="text-gray-500">Kategori</div>
      <div class="font-medium text-gray-800">{{ $ticket->kategori }}</div>
    </div>
    <div>
      <div class="text-gray-500">IT Handler</div>
      <div class="font-medium text-gray-800">{{ $ticket->it->name ?? '-' }}</div>
    </div>
  </div>

  @if($ticket->deskripsi)
    <p class="mt-3 text-sm text-gray-700 line-clamp-3">{{ $ticket->deskripsi }}</p>
  @endif

  <div class="mt-3 flex items-center gap-2">
    <a href="{{ route('ticket.show', $ticket->id) }}"
       class="inline-flex items-center rounded-lg bg-gray-900 px-3 py-1.5 text-white hover:bg-gray-800">
      Detail
    </a>

    @if($showActions && auth()->user()?->role === 'IT')
      {{-- Aksi tombol sesuai status/otorisasi --}}
      @if($ticket->status === 'OPEN')
        <form method="POST" action="{{ route('it.ticket.take', $ticket->id) }}">
          @csrf
          <button class="rounded-lg bg-indigo-600 px-3 py-1.5 text-white hover:bg-indigo-700">
            Ambil
          </button>
        </form>
      @elseif($ticket->status === 'ON_PROGRESS' && $ticket->it_id === auth()->id())
        <form method="POST" action="{{ route('it.ticket.release', $ticket->id) }}">
          @csrf
          <button class="rounded-lg bg-gray-200 px-3 py-1.5 text-gray-800 hover:bg-gray-300">
            Lepas
          </button>
        </form>
        <form method="POST" action="{{ route('it.ticket.close', $ticket->id) }}">
          @csrf
          <button class="rounded-lg bg-emerald-600 px-3 py-1.5 text-white hover:bg-emerald-700">
            Tutup
          </button>
        </form>
      @elseif($ticket->status === 'CLOSED')
        <form method="POST" action="{{ route('it.ticket.reopen', $ticket->id) }}">
          @csrf
          <button class="rounded-lg bg-amber-600 px-3 py-1.5 text-white hover:bg-amber-700">
            Reopen
          </button>
        </form>
      @endif
    @endif
  </div>
</div>
