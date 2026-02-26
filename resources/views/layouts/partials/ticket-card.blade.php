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

<div class="rounded-lg border border-gray-100 bg-white p-3 shadow-sm text-xs sm:text-sm">
  <div class="flex items-start justify-between gap-2">
    <div>
      <a href="{{ route('ticket.show', $ticket->id) }}"
         class="text-xs sm:text-sm font-semibold text-indigo-600 hover:underline">
        #{{ $ticket->nomor_tiket }}
      </a>
      <div class="mt-0.5 text-[11px] text-gray-500">
        Oleh <span class="font-medium text-gray-700">{{ $ticket->user->name ?? '-' }}</span>
        • {{ $ticket->created_at->format('d M Y H:i') }}
      </div>
    </div>
    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium ring-1 {{ $badge }}">
      {{ $ticket->status }}
    </span>
  </div>

  <div class="mt-2 grid grid-cols-2 gap-2 text-[11px]">
    <div>
      <div class="text-gray-500">Kategori</div>
      <div class="font-medium text-gray-800 truncate">{{ $ticket->kategori }}</div>
    </div>
    <div>
      <div class="text-gray-500">IT</div>
      <div class="font-medium text-gray-800 truncate">{{ $ticket->it->name ?? '-' }}</div>
    </div>
  </div>

  @if($ticket->deskripsi)
    <p class="mt-2 text-[12px] text-gray-700 line-clamp-2">{{ $ticket->deskripsi }}</p>
  @endif

  <div class="mt-2 flex items-center gap-2">
    <a href="{{ route('ticket.show', $ticket->id) }}"
       class="inline-flex items-center rounded-md bg-gray-900 px-2 py-1 text-xs text-white hover:bg-gray-800">
      Detail
    </a>

    @if($showActions && auth()->user()?->role === 'IT')
      @if($ticket->status === 'OPEN')
        <form method="POST" action="{{ route('it.ticket.take', $ticket->id) }}">
          @csrf
          <button class="rounded-md bg-indigo-600 px-2 py-1 text-xs text-white hover:bg-indigo-700">
            Ambil
          </button>
        </form>
      @elseif($ticket->status === 'ON_PROGRESS' && $ticket->it_id === auth()->id())
        <form method="POST" action="{{ route('it.ticket.release', $ticket->id) }}">
          @csrf
          <button class="rounded-md bg-gray-200 px-2 py-1 text-xs text-gray-800 hover:bg-gray-300">
            Lepas
          </button>
        </form>
        <form method="POST" action="{{ route('it.ticket.close', $ticket->id) }}">
          @csrf
          <button class="rounded-md bg-emerald-600 px-2 py-1 text-xs text-white hover:bg-emerald-700">
            Tutup
          </button>
        </form>
      @elseif($ticket->status === 'CLOSED')
        <form method="POST" action="{{ route('it.ticket.reopen', $ticket->id) }}">
          @csrf
          <button class="rounded-md bg-amber-600 px-2 py-1 text-xs text-white hover:bg-amber-700">
            Reopen
          </button>
        </form>
      @endif
    @endif
  </div>
</div>
