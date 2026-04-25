@extends('layouts.app')
@section('title','Daftar Tiket')

@section('content')
<div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-3 sm:p-5 text-xs sm:text-sm">
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

      {{-- Username pembuat tiket --}}
      <div class="order-1 md:order-none shrink-0 w-full md:w-[220px]">
        <input type="text" name="username" value="{{ request('username') }}" placeholder="Username pembuat"
               class="w-full h-10 rounded-lg border-gray-300 px-3 focus:border-indigo-500 focus:ring-indigo-500" />
      </div>

      {{-- Root Cause filter (di sebelah kanan kolom pencarian) --}}
      <div class="order-2 md:order-none shrink-0 w-full md:w-[220px]">
        <label class="sr-only">Root Cause</label>
        <select name="root_cause" class="w-full h-10 rounded-lg border-gray-300 px-3 focus:border-indigo-500 focus:ring-indigo-500">
          <option value="">Root Cause</option>
          @foreach($rootCauses ?? [] as $rc)
            <option value="{{ $rc }}" @selected(request('root_cause')===$rc)>{{ $rc }}</option>
          @endforeach
        </select>
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

        @if(request()->hasAny(['q','username','status','date_from','date_to','root_cause','category_id','subcategory_id','kategori']))
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

  @include('it._tickets')


<script>
// Tidak perlu JS untuk subkategori; pencarian digabung dalam kolom q
</script>
<script>
  // Polling: fetch tickets fragment and replace content if changed
  (function(){
    const intervalMs = 3000; // 3s
    const activeFilterKeys = ['q', 'username', 'status', 'date_from', 'date_to', 'root_cause', 'category_id', 'subcategory_id', 'kategori'];
    const queryParams = new URLSearchParams(window.location.search);
    const hasActiveFilter = activeFilterKeys.some((key) => {
      const value = queryParams.get(key);
      return value !== null && String(value).trim() !== '';
    });

    // Saat filter aktif, jangan auto-refresh list agar hasil filter tidak ketimpa polling.
    if (hasActiveFilter) return;

    const fragmentUrl = '{{ route("it.tickets.fragment") }}' + window.location.search;
    async function fetchFragment(){
      try{
        const res = await fetch(fragmentUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if(!res.ok) return;
        const html = await res.text();
        // create a container to parse returned HTML
        const tmp = document.createElement('div'); tmp.innerHTML = html;
        const newDesktop = tmp.querySelector('#tickets-fragment');
        const newMobile = tmp.querySelector('#tickets-fragment-mobile');
        const newPag = tmp.querySelector('#tickets-fragment-pagination');
        if(newDesktop){
          const oldDesktop = document.querySelector('#tickets-fragment');
          oldDesktop?.replaceWith(newDesktop);
        }
        if(newMobile){
          const oldMobile = document.querySelector('#tickets-fragment-mobile');
          oldMobile?.replaceWith(newMobile);
        }
        if(newPag){
          const oldPag = document.querySelector('#tickets-fragment-pagination');
          oldPag?.replaceWith(newPag);
        }
      }catch(e){
        // ignore errors
      }
    }
    // start polling after small delay
    setTimeout(() => { fetchFragment(); setInterval(fetchFragment, intervalMs); }, 3000);
  })();
</script>
@endsection
