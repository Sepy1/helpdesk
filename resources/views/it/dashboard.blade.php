@extends('layouts.app')
@section('title','Daftar Tiket')

@section('content')
<div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
  {{-- Header + Filters inline in a single row --}}
  <div class="flex flex-col gap-3 md:gap-4">
    <div class="flex items-center justify-center text-center">
      <div>
        <h2 class="text-lg font-semibold text-gray-800">Daftar Tiket</h2>
        <p class="text-sm text-gray-500">Klik nomor tiket untuk melihat detail.</p>
      </div>
    </div>

    {{-- Filter: gabung kategori & subkategori ke kolom pencarian --}}
    <form method="GET" class="w-full flex flex-col md:flex-row items-end justify-center gap-2 md:gap-2 md:flex-nowrap overflow-x-auto mb-4 md:mb-6" id="filter-form">
      <div class="order-1 md:order-none shrink-0 w-full md:w-[360px]">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nomor / deskripsi / kategori"
               class="w-full h-10 rounded-lg border-gray-300 px-3 focus:border-indigo-500 focus:ring-indigo-500" />
      </div>

      {{-- Status --}}
      <div class="order-4 md:order-none shrink-0 w-full md:w-[150px]">
        <label class="sr-only">Status</label>
        <select name="status" class="w-full h-10 rounded-lg border-gray-300 px-3 focus:border-indigo-500 focus:ring-indigo-500">
          <option value="">Status</option>
          @foreach(['OPEN','ON_PROGRESS','ESKALASI_VENDOR','VENDOR_RESOLVED','CLOSED'] as $s)
            <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
          @endforeach
        </select>
      </div>

      {{-- Range tanggal --}}
      <div class="shrink-0 flex flex-col md:flex-row gap-2 w-full md:w-auto">
        <div class="order-2 md:order-none shrink-0 w-full md:w-[150px]">
          <input type="text" name="date_from" value="{{ request('date_from') }}" placeholder="Tgl Awal" inputmode="numeric" pattern="\d{4}-\d{2}-\d{2}"
                 class="w-full h-10 rounded-lg border-gray-300 px-3 focus:border-indigo-500 focus:ring-indigo-500"
                 onfocus="this.type='date'" onblur="if(!this.value) this.type='text'" />
        </div>
        <div class="order-3 md:order-none shrink-0 w-full md:w-[150px]">
          <input type="text" name="date_to" value="{{ request('date_to') }}" placeholder="Tgl Akhir" inputmode="numeric" pattern="\d{4}-\d{2}-\d{2}"
                 class="w-full h-10 rounded-lg border-gray-300 px-3 focus:border-indigo-500 focus:ring-indigo-500"
                 onfocus="this.type='date'" onblur="if(!this.value) this.type='text'" />
        </div>
      </div>

      {{-- Buttons: Filter + Reset + Export --}}
      <div class="order-5 md:order-none shrink-0 flex gap-2 justify-center w-full md:w-auto">
        <button type="submit" class="w-full md:w-auto h-10 rounded-lg bg-gradient-to-r from-blue-500 to-sky-500 text-white px-4">Filter</button>

        @if(request()->hasAny(['q','status','date_from','date_to']))
          <a href="{{ route('it.dashboard') }}"
             class="shrink-0 h-10 inline-block text-center rounded-lg border border-gray-200 px-4 text-sm text-gray-700 hover:underline leading-10">
             Reset
           </a>
            <a href="{{ route('it.tickets.export', request()->query()) }}"
              class="shrink-0 h-10 inline-block text-center rounded-lg bg-emerald-600 px-4 text-white hover:bg-emerald-700 leading-10">
             Export Result
           </a>
        @endif
      </div>
    </form>
  </div>

  {{-- ===== Desktop: tabel ===== --}}
  <div class="hidden md:block overflow-x-auto">
    <table class="min-w-full text-sm table-fixed">
      <colgroup>
        <col style="width:4%">
        <col style="width:18%">
        <col style="width:18%">
        <col style="width:20%">
        <col style="width:12%">
        <col style="width:18%">
        <col style="width:10%">
      </colgroup>
      <thead class="bg-gray-50 text-gray-600">
        <tr>
          <th class="py-3 px-4 text-left whitespace-nowrap">#</th>
          <th class="py-3 px-4 text-left whitespace-nowrap">Nomor</th>
          <th class="py-3 px-4 text-left whitespace-nowrap">Kategori</th>
          <th class="py-3 px-4 text-left whitespace-nowrap">Pembuat</th>
          <th class="py-3 px-4 text-left whitespace-nowrap">Status</th>
          <th class="py-3 px-4 text-left whitespace-nowrap">IT Handler</th>
          <th class="py-3 px-4 text-left whitespace-nowrap">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        @foreach($tickets as $i => $t)
        <tr class="hover:bg-gray-50">
          <td class="py-3 px-4 text-gray-500">{{ $tickets->firstItem()+$i }}</td>
          <td class="py-3 px-4 font-medium truncate">
            <a href="{{ route('ticket.show',$t->id) }}" class="text-indigo-600 hover:underline block truncate">{{ $t->nomor_tiket }}</a>
          </td>
          <td class="py-3 px-4 truncate">{{ $t->kategori }}</td>
          <td class="py-3 px-4 truncate">{{ $t->user->name ?? '-' }}</td>
          <td class="py-3 px-4">
            @php
              $badge = match($t->status){
                'OPEN'             => 'bg-gray-100 text-gray-700 ring-gray-200',
                'ON_PROGRESS'      => 'bg-amber-100 text-amber-800 ring-amber-200',
                'ESKALASI_VENDOR'  => 'bg-fuchsia-100 text-fuchsia-800 ring-fuchsia-200',
                'VENDOR_RESOLVED'  => 'bg-indigo-100 text-indigo-800 ring-indigo-200',
                'CLOSED'           => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
                default            => 'bg-gray-100 text-gray-700 ring-gray-200',
              };
            @endphp
            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 {{ $badge }}">{{ $t->status }}</span>
          </td>
          <td class="py-3 px-4 truncate">{{ $t->it->name ?? '-' }}</td>
          <td class="py-3 px-4 space-x-1 whitespace-nowrap">
            <a href="{{ route('ticket.show',$t->id) }}" class="inline-flex items-center rounded-lg bg-gradient-to-r from-blue-500 to-sky-500 px-3 py-1.5 text-tulisan-50 hover:bg-gray-200">Detail</a>
            @if($t->status==='OPEN' || ($t->status!=='CLOSED' && !$t->it_id))
              <form method="POST" class="inline" action="{{ route('it.ticket.take',$t->id) }}">@csrf
                <button class="rounded-lg bg-indigo-600 px-3 py-1.5 text-white hover:bg-indigo-700">Take</button>
              </form>
            @elseif($t->it_id===auth()->id() && $t->status==='ON_PROGRESS')
              <form method="POST" class="inline" action="{{ route('it.ticket.release',$t->id) }}">@csrf
                <button class="rounded-lg bg-brand-700 px-3 py-1.5 text-tulisan-50 hover:bg-gray-300">Lepas</button>
              </form>
              <form method="POST" class="inline" action="{{ route('it.ticket.close',$t->id) }}">@csrf
                <button class="rounded-lg bg-emerald-600 px-3 py-1.5 text-white hover:bg-emerald-700">Tutup</button>
              </form>
            @else
              <span class="text-xs text-gray-500">Sudah diambil</span>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
   {{-- ===== Mobile: card per tiket ===== --}}
  <div class="block md:hidden space-y-3">
    @forelse($tickets as $t)
      <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between gap-3">
          <div>
            <a href="{{ route('ticket.show',$t->id) }}" class="font-semibold text-indigo-600 hover:underline">
              {{ $t->nomor_tiket }}
            </a>
            <div class="mt-1 text-xs text-gray-500">
              Dibuat: {{ $t->created_at->format('d M Y H:i') }}
            </div>
          </div>

          @php
            $badge = match($t->status){
              'OPEN'             => 'bg-gray-100 text-gray-700 ring-gray-200',
              'ON_PROGRESS'      => 'bg-amber-100 text-amber-800 ring-amber-200',
              'ESKALASI_VENDOR'  => 'bg-fuchsia-100 text-fuchsia-800 ring-fuchsia-200',
              'VENDOR_RESOLVED'  => 'bg-indigo-100 text-indigo-800 ring-indigo-200',
              'CLOSED'           => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
              default            => 'bg-gray-100 text-gray-700 ring-gray-200',
            };
          @endphp
          <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium ring-1 {{ $badge }}">{{ $t->status }}</span>
        </div>

        <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
          <div class="text-gray-500">Kategori</div><div class="font-medium truncate">{{ $t->kategori }}</div>
          <div class="text-gray-500">Pembuat</div><div class="font-medium truncate">{{ $t->user->name ?? '-' }}</div>
          <div class="text-gray-500">Handler</div><div class="font-medium truncate">{{ $t->it->name ?? '-' }}</div>
        </div>

        <div class="mt-3 flex flex-wrap gap-2">
          <a href="{{ route('ticket.show',$t->id) }}" class="rounded-lg bg-gradient-to-r from-blue-500 to-sky-500 px-3 py-2 text-tulisan-50 hover:bg-gray-800">Detail</a>

          @if($t->status==='OPEN' || ($t->status!=='CLOSED' && !$t->it_id))
            <form method="POST" action="{{ route('it.ticket.take',$t->id) }}">@csrf
              <button class="rounded-lg bg-indigo-600 px-3 py-2 text-white hover:bg-indigo-700 w-full sm:w-auto">Ambil Alih</button>
            </form>
          @elseif($t->it_id===auth()->id() && $t->status==='ON_PROGRESS')
            <form method="POST" action="{{ route('it.ticket.release',$t->id) }}">@csrf
              <button class="rounded-lg bg-gray-200 px-3 py-2 text-gray-800 hover:bg-gray-300 w-full sm:w-auto">Lepas</button>
            </form>
            <form method="POST" action="{{ route('it.ticket.close',$t->id) }}">@csrf
              <button class="rounded-lg bg-emerald-600 px-3 py-2 text-white hover:bg-emerald-700 w-full sm:w-auto">Tutup</button>
            </form>
          @else
            <span class="text-xs text-gray-500 self-center">Sudah diambil</span>
          @endif
        </div>
      </div>
    @empty
      <div class="text-center text-gray-500 py-8">Tidak ada tiket.</div>
    @endforelse
  </div>
{{-- Pagination: showing kiri + paginate center (hapus duplikat) --}}
<div class="mt-4">
  <div class="grid grid-cols-1 md:grid-cols-3 items-center gap-3">
    {{-- LEFT: showing (tetap di kiri pada desktop, atas pada mobile) --}}
    <div class="text-sm text-gray-500 text-center md:text-left min-w-0">
      Tiket {{ $tickets->firstItem() }} sampai {{ $tickets->lastItem() }} dari total {{ $tickets->total() }} Tiket
    </div>

    {{-- CENTER: pagination (selalu center) --}}
    <div class="flex justify-center">
      {!! $tickets->appends(request()->except('page'))->links('pagination::tailwind') !!}
    </div>

    {{-- RIGHT: spacer (kosong) supaya pagination tetap di tengah dan tidak ada teks duplikat) --}}
    <div></div>
  </div>
</div>


<script>
// Tidak perlu JS untuk subkategori; pencarian digabung dalam kolom q
</script>
@endsection
