{{-- Partial: tickets list (desktop table + mobile cards + pagination) --}}

  {{-- ===== Desktop: tabel ===== --}}
  <div class="hidden max-h-[calc(100dvh-15rem)] overflow-y-auto overflow-x-hidden overscroll-contain rounded-lg border border-gray-100 md:block" id="tickets-fragment">
    <table class="min-w-full text-sm table-fixed">
      <colgroup>
        <col style="width:12%">  <!-- Dibuat -->
        <col style="width:14%">  <!-- Nomor -->
        <col style="width:11%">  <!-- Kategori -->
        <col style="width:11%">  <!-- Subkategori -->
        <col style="width:10%">  <!-- Root Cause -->
        <col style="width:12%">  <!-- Pembuat -->
        <col style="width:12%">  <!-- Status -->
        <col style="width:10%">  <!-- IT Handler -->
        <col style="width:8%">   <!-- Aksi -->
      </colgroup>
      <thead class="hd-table-head sticky top-0 z-10 shadow-sm">
        <tr>
          <th class="py-3 px-4 text-left whitespace-nowrap">Dibuat</th>
          <th class="py-3 px-4 text-left whitespace-nowrap">Nomor</th>
          <th class="py-3 px-4 text-left whitespace-nowrap">Kategori</th>
          <th class="py-3 px-4 text-left whitespace-nowrap">Subkategori</th>
          <th class="py-3 px-4 text-left whitespace-nowrap">Root Cause</th>
          <th class="py-3 px-4 text-left whitespace-nowrap">Pembuat</th>
          <th class="py-3 px-4 text-left whitespace-nowrap">Status</th>
          <th class="py-3 px-4 text-left whitespace-nowrap">IT Handler</th>
          <th class="py-3 px-4 text-left whitespace-nowrap">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        @foreach($tickets as $i => $t)
        <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-hd-50/60">
          <td class="py-3 px-4 whitespace-nowrap">{{ optional($t->created_at)->format('d M Y H:i') ?? '-' }}</td>
          <td class="py-3 px-4 font-medium truncate">
            <a href="{{ route('ticket.show',$t->id) }}" class="hd-link block truncate">{{ $t->nomor_tiket }}</a>
          </td>
          <td class="py-3 px-4 truncate">{{ $t->kategori }}</td>
          <td class="py-3 px-4 truncate">{{ optional($t->subcategory)->name ?? '-' }}</td>
          <td class="py-3 px-4 truncate">{{ $t->root_cause ?? '-' }}</td>
          <td class="py-3 px-4 truncate">{{ $t->user->name ?? '-' }}</td>
          <td class="py-3 px-4">
            @php
              $badge = match($t->status){
                'OPEN'             => 'bg-red-100 text-red-800 ring-red-200',
                'ON_PROGRESS'      => 'bg-yellow-100 text-yellow-800 ring-yellow-200',
                'ESKALASI_VENDOR'  => 'bg-purple-100 text-purple-800 ring-purple-200',
                'VENDOR_RESOLVED'  => 'bg-black text-white ring-black',
                'CLOSED'           => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
                default            => 'bg-gray-100 text-gray-700 ring-gray-200',
              };
            @endphp
            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 {{ $badge }}">{{ $t->status }}</span>
          </td>
          <td class="py-3 px-4 truncate">{{ $t->it->name ?? '-' }}</td>
          <td class="py-3 px-4 space-x-1 whitespace-nowrap">
            <a href="{{ route('ticket.show',$t->id) }}" class="hd-btn-detail">Detail</a>
            @if($t->status !== 'CLOSED')
              <form method="POST" class="inline" action="{{ route('it.ticket.take',$t->id) }}">@csrf
                <button class="hd-btn-take">Take</button>
              </form>
              @if($t->it_id===auth()->id() && $t->status==='ON_PROGRESS')
                <form method="POST" class="inline" action="{{ route('it.ticket.release',$t->id) }}">@csrf
                  <button class="hd-btn-lepas">Lepas</button>
                </form>
              @endif
            @else
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
   {{-- ===== Mobile: card per tiket ===== --}}
  <div class="block max-h-[55dvh] space-y-3 overflow-y-auto overscroll-contain rounded-lg pr-1 md:hidden" id="tickets-fragment-mobile">
    @forelse($tickets as $t)
      <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between gap-3">
          <div>
            <a href="{{ route('ticket.show',$t->id) }}" class="font-semibold hd-link">
              {{ $t->nomor_tiket }}
            </a>
            <div class="mt-1 text-xs text-gray-500">
              Dibuat: {{ optional($t->created_at)->format('d M Y H:i') ?? '-' }}
            </div>
          </div>

          @php
            $badge = match($t->status){
              'OPEN'             => 'bg-red-100 text-red-800 ring-red-200',
              'ON_PROGRESS'      => 'bg-yellow-100 text-yellow-800 ring-yellow-200',
              'ESKALASI_VENDOR'  => 'bg-purple-100 text-purple-800 ring-purple-200',
              'VENDOR_RESOLVED'  => 'bg-black text-white ring-black',
              'CLOSED'           => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
              default            => 'bg-gray-100 text-gray-700 ring-gray-200',
            };
          @endphp
          <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium ring-1 {{ $badge }}">{{ $t->status }}</span>
        </div>

          <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
          <div class="text-gray-500">Kategori</div><div class="font-medium truncate">{{ $t->kategori }}</div>
          <div class="text-gray-500">Subkategori</div><div class="font-medium truncate">{{ optional($t->subcategory)->name ?? '-' }}</div>
          <div class="text-gray-500">Root Cause</div><div class="font-medium truncate">{{ $t->root_cause ?? '-' }}</div>
          <div class="text-gray-500">Pembuat</div><div class="font-medium truncate">{{ $t->user->name ?? '-' }}</div>
          <div class="text-gray-500">Handler</div><div class="font-medium truncate">{{ $t->it->name ?? '-' }}</div>
        </div>

        <div class="mt-3 flex flex-wrap gap-2">
          <a href="{{ route('ticket.show',$t->id) }}" class="hd-btn-detail px-3 py-2">Detail</a>

          @if($t->status !== 'CLOSED')
            <form method="POST" action="{{ route('it.ticket.take',$t->id) }}">@csrf
              <button class="hd-btn-take px-3 py-2 w-full sm:w-auto">Ambil Alih</button>
            </form>
            @if($t->it_id===auth()->id() && $t->status==='ON_PROGRESS')
              <form method="POST" action="{{ route('it.ticket.release',$t->id) }}">@csrf
                <button class="hd-btn-lepas px-3 py-2 w-full sm:w-auto">Lepas</button>
              </form>
              <form method="POST" action="{{ route('it.ticket.close',$t->id) }}">@csrf
                <button class="rounded-lg bg-emerald-600 px-3 py-2 text-white hover:bg-emerald-700 w-full sm:w-auto">Tutup</button>
              </form>
            @endif
          @else
          @endif
        </div>
      </div>
    @empty
      <div class="text-center text-gray-500 py-8">Tidak ada tiket.</div>
    @endforelse
  </div>
<div id="tickets-infinite-loading" class="hidden items-center justify-center gap-2 py-2 text-xs text-gray-500" role="status" aria-live="polite">
  <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4Z"></path>
  </svg>
  <span>Memuat tiket berikutnya...</span>
</div>
{{-- Pagination: showing kiri + paginate center (hapus duplikat) --}}
<div class="mt-2 shrink-0" id="tickets-fragment-pagination">
  <div class="grid grid-cols-1 md:grid-cols-3 items-center gap-3">
    <div class="text-sm text-gray-500 text-center md:text-left min-w-0">
      Tiket {{ $tickets->firstItem() }} sampai {{ $tickets->lastItem() }} dari total {{ $tickets->total() }} Tiket
    </div>

    <div class="flex justify-center">
      {!! $tickets->withPath(route('it.dashboard'))->appends(request()->except('page'))->links('pagination::tailwind') !!}
    </div>

    <div></div>
  </div>
</div>
