@extends('layouts.app')
@section('title','Daftar Tiket')

@push('styles')
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@section('content')
<div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-3 sm:p-5 text-xs sm:text-sm">
  {{-- Header + Filters inline in a single row --}}
  <div class="flex flex-col gap-3 md:gap-4">
    <div class="flex items-center justify-center text-center">
      <div>
        <h2 class="text-lg font-semibold text-gray-800">Daftar Tiket</h2>
        <p class="text-sm text-gray-500">Klik nomor tiket untuk melihat detail. Filter diterapkan otomatis.</p>
      </div>
    </div>

    @php
      $summaryScope = array_filter([
        'kode_kantor' => request('kode_kantor'),
        'date_from' => request('date_from'),
        'date_to' => request('date_to'),
      ], fn ($value) => $value !== null && $value !== '');
      $summaryCards = [
        ['label' => 'Total Tiket', 'value' => $ticketSummary['total'] ?? 0, 'query' => [], 'tone' => 'indigo'],
        ['label' => 'Tiket Open', 'value' => $ticketSummary['open'] ?? 0, 'query' => ['status' => 'OPEN'], 'tone' => 'sky'],
        ['label' => 'Tiket On Progress', 'value' => $ticketSummary['on_progress'] ?? 0, 'query' => ['status' => 'ON_PROGRESS'], 'tone' => 'amber'],
        ['label' => 'Tiket Closed', 'value' => $ticketSummary['closed'] ?? 0, 'query' => ['status' => 'CLOSED'], 'tone' => 'emerald'],
        ['label' => 'Melebihi SLA', 'value' => $ticketSummary['sla_exceeded'] ?? 0, 'query' => ['sla_exceeded' => 1], 'tone' => 'rose'],
      ];
      $summaryTones = [
        'indigo' => 'border-indigo-200 bg-indigo-50 text-indigo-700',
        'sky' => 'border-sky-200 bg-sky-50 text-sky-700',
        'amber' => 'border-amber-200 bg-amber-50 text-amber-700',
        'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'rose' => 'border-rose-200 bg-rose-50 text-rose-700',
      ];
    @endphp
    <div class="grid grid-cols-2 gap-1.5 sm:grid-cols-3 lg:grid-cols-5" aria-label="Ringkasan tiket">
      @foreach($summaryCards as $card)
        @php
          $isActive = $card['query'] === []
            ? !request()->filled('status') && !request()->boolean('sla_exceeded')
            : collect($card['query'])->every(fn ($value, $key) => (string) request($key) === (string) $value);
        @endphp
        <a href="{{ route('it.dashboard', array_merge($summaryScope, $card['query'])) }}"
           class="group flex min-h-12 items-center justify-between gap-2 rounded-lg border px-2.5 py-1.5 transition hover:-translate-y-0.5 hover:shadow-md sm:px-3 {{ $summaryTones[$card['tone']] }} {{ $isActive ? 'ring-2 ring-current ring-offset-1' : '' }}"
           @if($card['label'] === 'Melebihi SLA') title="Tiket aktif yang melewati SLA kategori masing-masing" @endif>
          <span class="min-w-0 text-[10px] font-semibold uppercase leading-tight tracking-wide opacity-80 sm:text-xs">{{ $card['label'] }}</span>
          <span class="shrink-0 text-lg font-bold tabular-nums sm:text-xl">{{ number_format($card['value']) }}</span>
        </a>
      @endforeach
    </div>

    {{-- Filter: gabung kategori & subkategori ke kolom pencarian --}}
    <form method="GET" class="w-full flex flex-col xl:flex-row xl:flex-nowrap items-end gap-2 mb-4 md:mb-6" id="filter-form">
      @if(request()->boolean('sla_exceeded'))
        <input type="hidden" name="sla_exceeded" value="1">
      @endif
      <div class="w-full xl:w-[260px] xl:shrink-0">
        <input type="text" id="filter-q" name="q" value="{{ request('q') }}" placeholder="Cari nomor / deskripsi / kategori"
               class="w-full h-10 rounded-lg border-gray-300 px-3 focus:border-indigo-500 focus:ring-indigo-500" autocomplete="off" />
      </div>

      {{-- Kode kantor pembuat tiket --}}
      <div class="w-full min-w-0 xl:w-[220px] xl:shrink-0">
        <label class="sr-only">Kode kantor pembuat</label>
        <select name="kode_kantor" class="w-full h-10 rounded-lg border-gray-300 px-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
          <option value="">Semua kantor</option>
          @foreach($kodeKantors ?? [] as $kk)
            <option value="{{ $kk->kode }}" @selected(request('kode_kantor') === $kk->kode)>{{ $kk->kode }} — {{ $kk->nama_kantor }}</option>
          @endforeach
        </select>
      </div>

      {{-- Root Cause filter (di sebelah kanan kolom pencarian) --}}
      <div class="w-full xl:w-[170px] xl:shrink-0">
        <label class="sr-only">Root Cause</label>
        <select name="root_cause" class="w-full h-10 rounded-lg border-gray-300 px-3 focus:border-indigo-500 focus:ring-indigo-500">
          <option value="">Root Cause</option>
          @foreach($rootCauses ?? [] as $rc)
            <option value="{{ $rc }}" @selected(request('root_cause')===$rc)>{{ $rc }}</option>
          @endforeach
        </select>
      </div>

      {{-- Status --}}
      <div class="w-full xl:w-[130px] xl:shrink-0">
        <label class="sr-only">Status</label>
        <select name="status" class="w-full h-10 rounded-lg border-gray-300 px-3 focus:border-indigo-500 focus:ring-indigo-500">
          <option value="">Status</option>
          @foreach(['OPEN','ON_PROGRESS','ESKALASI_VENDOR','VENDOR_RESOLVED','CLOSED'] as $s)
            <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
          @endforeach
        </select>
      </div>

      {{-- Range tanggal --}}
      <div class="w-full xl:w-[260px] xl:shrink-0">
        <label class="sr-only">Periode Tanggal</label>
        <input type="text" id="date-range" name="date_range"
               value="{{ request('date_from') && request('date_to') ? request('date_from').' - '.request('date_to') : '' }}"
               placeholder="Periode Tanggal (YYYY-MM-DD - YYYY-MM-DD)"
               class="w-full h-10 rounded-lg border-gray-300 px-3 focus:border-indigo-500 focus:ring-indigo-500" />
        <input type="hidden" name="date_from" id="date-from-hidden" value="{{ request('date_from') }}">
        <input type="hidden" name="date_to" id="date-to-hidden" value="{{ request('date_to') }}">
      </div>

      {{-- Reset + Export (filter utama otomatis) --}}
      <div class="w-full xl:flex-1 flex flex-wrap xl:flex-nowrap gap-2 justify-start xl:justify-end">
        <button type="submit" class="w-full md:w-auto h-10 rounded-lg border border-gray-200 bg-white px-4 text-sm text-gray-700 hover:bg-gray-50" title="Muat ulang dengan nilai filter saat ini">Muat ulang</button>

        <a href="{{ route('it.dashboard') }}"
           class="shrink-0 h-10 inline-block text-center rounded-lg border border-gray-200 px-4 text-sm text-gray-700 hover:underline leading-10">
           Reset
         </a>
        <a href="{{ route('it.tickets.export', request()->query()) }}"
            class="shrink-0 h-10 inline-block text-center rounded-lg hd-btn-export leading-10">
           Export Result
         </a>
      </div>
    </form>
  </div>

  @include('it._tickets')


<script>
// Parse periode tanggal (teks range) -> hidden date_from / date_to (dipakai submit manual & otomatis)
(function () {
  const form = document.getElementById('filter-form');
  const rangeInput = document.getElementById('date-range');
  const fromInput = document.getElementById('date-from-hidden');
  const toInput = document.getElementById('date-to-hidden');
  if (!form || !rangeInput || !fromInput || !toInput) return;

  window.__parseTicketDashboardDates = function () {
    const raw = (rangeInput.value || '').trim();
    if (!raw) {
      fromInput.value = '';
      toInput.value = '';
      return;
    }
    const parts = raw.split(/\s+-\s+|\s+s\/d\s+|\s+to\s+/i).map(function (s) { return s.trim(); }).filter(Boolean);
    if (parts.length >= 2) {
      fromInput.value = parts[0];
      toInput.value = parts[1];
    } else {
      fromInput.value = parts[0] || '';
      toInput.value = parts[0] || '';
    }
  };

  form.addEventListener('submit', function () {
    window.__parseTicketDashboardDates();
  });
})();
</script>
<script>
  // Polling: fetch tickets fragment and replace content if changed
  (function(){
    const intervalMs = 3000; // 3s
    const activeFilterKeys = ['q', 'kode_kantor', 'status', 'sla_exceeded', 'date_from', 'date_to', 'root_cause', 'category_id', 'subcategory_id', 'kategori'];
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
        if (window.__ticketInfiniteLoadingStarted || window.__ticketInfiniteHasLoadedMore) return;
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
<script>
  // Infinite scroll: tambahkan halaman berikutnya saat area daftar mendekati batas bawah.
  (function () {
    let loading = false;
    let finished = false;

    function nextPageUrl() {
      const nextLink = document.querySelector('#tickets-fragment-pagination a[rel="next"]');
      if (!nextLink) return null;

      const dashboardUrl = new URL(nextLink.href, window.location.origin);
      const fragmentUrl = new URL(@json(route('it.tickets.fragment')), window.location.origin);
      fragmentUrl.search = dashboardUrl.search;
      return fragmentUrl.toString();
    }

    function setLoading(isLoading) {
      const indicator = document.getElementById('tickets-infinite-loading');
      if (!indicator) return;
      indicator.classList.toggle('hidden', !isLoading);
      indicator.classList.toggle('flex', isLoading);
    }

    async function loadNextPage() {
      if (loading || finished) return;
      const url = nextPageUrl();
      if (!url) {
        finished = true;
        return;
      }

      loading = true;
      window.__ticketInfiniteLoadingStarted = true;
      setLoading(true);
      try {
        const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (!response.ok) throw new Error('Gagal memuat halaman berikutnya');

        const holder = document.createElement('div');
        holder.innerHTML = await response.text();

        const currentBody = document.querySelector('#tickets-fragment tbody');
        const nextBody = holder.querySelector('#tickets-fragment tbody');
        if (currentBody && nextBody) {
          Array.from(nextBody.children).forEach((row) => currentBody.appendChild(row));
        }

        const currentMobile = document.getElementById('tickets-fragment-mobile');
        const nextMobile = holder.querySelector('#tickets-fragment-mobile');
        if (currentMobile && nextMobile) {
          Array.from(nextMobile.children).forEach((card) => currentMobile.appendChild(card));
        }

        const currentPagination = document.getElementById('tickets-fragment-pagination');
        const nextPagination = holder.querySelector('#tickets-fragment-pagination');
        if (currentPagination && nextPagination) currentPagination.replaceWith(nextPagination);

        window.__ticketInfiniteHasLoadedMore = true;
        finished = !nextPageUrl();
      } catch (error) {
        // Pagination biasa tetap tersedia sebagai fallback.
        window.__ticketInfiniteLoadingStarted = false;
      } finally {
        loading = false;
        setLoading(false);
      }
    }

    document.addEventListener('scroll', function (event) {
      const scroller = event.target;
      if (!(scroller instanceof HTMLElement)) return;
      if (scroller.id !== 'tickets-fragment' && scroller.id !== 'tickets-fragment-mobile') return;

      const distanceToBottom = scroller.scrollHeight - scroller.scrollTop - scroller.clientHeight;
      if (distanceToBottom <= 120) loadNextPage();
    }, true);
  })();
</script>
@endsection

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const form = document.getElementById('filter-form');
      const rangeInput = document.getElementById('date-range');
      const fromInput = document.getElementById('date-from-hidden');
      const toInput = document.getElementById('date-to-hidden');
      const qInput = document.getElementById('filter-q');
      if (!form || !rangeInput || !fromInput || !toInput || typeof flatpickr !== 'function') return;

      function applyFiltersFromForm() {
        if (typeof window.__parseTicketDashboardDates === 'function') {
          window.__parseTicketDashboardDates();
        }
        if (typeof form.requestSubmit === 'function') {
          form.requestSubmit();
        } else {
          form.submit();
        }
      }

      let qDebounce;
      if (qInput) {
        qInput.addEventListener('input', function () {
          clearTimeout(qDebounce);
          qDebounce = setTimeout(function () { applyFiltersFromForm(); }, 450);
        });
      }

      form.querySelectorAll('select').forEach(function (sel) {
        sel.addEventListener('change', function () {
          // Memilih status biasa menggantikan mode khusus "Melebihi SLA".
          if (sel.name === 'status' && sel.value) {
            form.querySelector('input[name="sla_exceeded"]')?.remove();
          }
          applyFiltersFromForm();
        });
      });

      rangeInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          applyFiltersFromForm();
        }
      });

      flatpickr(rangeInput, {
        mode: 'range',
        dateFormat: 'Y-m-d',
        allowInput: true,
        defaultDate: (fromInput.value && toInput.value) ? [fromInput.value, toInput.value] : null,
        onChange: function (selectedDates, dateStr, instance) {
          if (selectedDates.length === 2) {
            fromInput.value = instance.formatDate(selectedDates[0], 'Y-m-d');
            toInput.value = instance.formatDate(selectedDates[1], 'Y-m-d');
            return;
          }
          if (selectedDates.length === 1) {
            const single = instance.formatDate(selectedDates[0], 'Y-m-d');
            fromInput.value = single;
            toInput.value = single;
            return;
          }
          fromInput.value = '';
          toInput.value = '';
        },
        onClose: function () {
          applyFiltersFromForm();
        }
      });
    });
  </script>
@endpush
