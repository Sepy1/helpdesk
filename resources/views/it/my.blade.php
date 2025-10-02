@extends('layouts.app')
@section('title','Tiket Saya (IT)')

@section('content')
<div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
  <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between mb-4">
  <div>
    <h2 class="text-lg font-semibold text-gray-800">Tiket yang Saya Tangani</h2>
    <p class="text-sm text-gray-500">Hanya tiket dengan <span class="font-medium">it_id = {{ auth()->id() }}</span>.</p>
  </div>
<form method="GET" class="space-y-3 md:space-y-0 md:flex md:flex-wrap md:items-end md:gap-2" id="filter-form">
      <div class="w-full md:w-auto md:flex-1">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nomor / deskripsi"
               class="w-full rounded-lg border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500" />
      </div>

      {{-- Kategori --}}
      <div class="w-full md:w-56">
        <label class="sr-only">Kategori</label>
        <select name="category_id" id="filter-category"
                class="w-full rounded-lg border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500">
          <option value="">Semua Kategori</option>
          @foreach($categories as $cat)
            <option value="{{ $cat->id }}" @selected((int)request('category_id') === $cat->id || (isset($selectedCategoryId) && (int)$selectedCategoryId === $cat->id))>
              {{ $cat->name }}
            </option>
          @endforeach
        </select>
      </div>

      {{-- Subkategori --}}
      <div class="w-full md:w-56">
        <label class="sr-only">Subkategori</label>
        <select name="subcategory_id" id="filter-subcategory"
                class="w-full rounded-lg border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500">
          <option value="">Semua Subkategori</option>
          @if(!empty($subcategories) && $subcategories->count())
            @foreach($subcategories as $s)
              <option value="{{ $s->id }}" @selected((int)request('subcategory_id') === $s->id)>{{ $s->name }}</option>
            @endforeach
          @endif
        </select>
      </div>

      {{-- Status --}}
      <div class="w-full md:w-40">
        <label class="sr-only">Status</label>
        <select name="status" class="w-full rounded-lg border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500">
          <option value="">Status</option>
          @foreach(['OPEN','ON_PROGRESS','CLOSED'] as $s)
            <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
          @endforeach
        </select>
      </div>

      {{-- Buttons: Filter + Reset --}}
      <div class="w-full md:w-auto flex flex-wrap gap-2">
        <button type="submit" class="w-full md:w-auto rounded-lg bg-gradient-to-r from-blue-500 to-sky-500 text-white px-4 py-2">Filter</button>

        @if(request()->hasAny(['q','category_id','subcategory_id','status','kategori']))
          <a href="{{ route('it.dashboard') }}"
             class="w-full md:w-auto inline-block text-center rounded-lg border border-gray-200 px-4 py-2 text-sm text-gray-700 hover:underline">
             Reset
          </a>
        @endif
      </div>
    </form>
  </div>
  
  {{-- Desktop table --}}
  <div class="hidden md:block overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-gray-50 text-gray-600">
        <tr>
          <th class="py-3 px-4 text-left">#</th>
          <th class="py-3 px-4 text-left">Nomor</th>
          <th class="py-3 px-4 text-left">Kategori</th>
          <th class="py-3 px-4 text-left">Pembuat</th>
          <th class="py-3 px-4 text-left">Status</th>
          <th class="py-3 px-4 text-left">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        @foreach($tickets as $i => $t)
        <tr class="hover:bg-gray-50">
          <td class="py-3 px-4 text-gray-500">{{ $tickets->firstItem()+$i }}</td>
          <td class="py-3 px-4 font-medium"><a href="{{ route('ticket.show',$t->id) }}" class="text-indigo-600 hover:underline">{{ $t->nomor_tiket }}</a></td>
          <td class="py-3 px-4">{{ $t->kategori }}</td>
          <td class="py-3 px-4">{{ $t->user->name ?? '-' }}</td>
          <td class="py-3 px-4">
            @php $badge = $t->status==='OPEN'?'bg-gray-100 text-gray-700 ring-gray-200':($t->status==='ON_PROGRESS'?'bg-amber-100 text-amber-800 ring-amber-200':'bg-emerald-100 text-emerald-800 ring-emerald-200'); @endphp
            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 {{ $badge }}">{{ $t->status }}</span>
          </td>
          <td class="py-3 px-4 space-x-1">
            <a href="{{ route('ticket.show',$t->id) }}" class="inline-flex items-center rounded-lg bg-gradient-to-r from-blue-500 to-sky-500 px-3 py-1.5 text-tulisan-50 hover:bg-gray-200">Detail</a>
            @if($t->status==='ON_PROGRESS')
              <form method="POST" class="inline" action="{{ route('it.ticket.release',$t->id) }}">@csrf
                <button class="rounded-lg bg-brand-700 px-3 py-1.5 text-tulisan-50 hover:bg-gray-300">Lepas</button>
              <form method="POST" class="inline" action="{{ route('it.ticket.close',$t->id) }}">@csrf
                <button class="rounded-lg bg-emerald-600 px-3 py-1.5 text-white hover:bg-emerald-700">Tutup</button>
              </form>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- Mobile cards --}}
  <div class="md:hidden space-y-3">
    @forelse($tickets as $t)
      <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between gap-3">
          <div>
            <a href="{{ route('ticket.show',$t->id) }}" class="font-semibold text-indigo-600 hover:underline">{{ $t->nomor_tiket }}</a>
            <div class="mt-1 text-xs text-gray-500">Dibuat: {{ $t->created_at->format('d M Y H:i') }}</div>
          </div>
          @php $badge = $t->status==='OPEN'?'bg-gray-100 text-gray-700 ring-gray-200':($t->status==='ON_PROGRESS'?'bg-amber-100 text-amber-800 ring-amber-200':'bg-emerald-100 text-emerald-800 ring-emerald-200'); @endphp
          <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 {{ $badge }}">{{ $t->status }}</span>
        </div>
        <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
          <div class="text-gray-500">Kategori</div><div class="font-medium">{{ $t->kategori }}</div>
          <div class="text-gray-500">Pembuat</div><div class="font-medium">{{ $t->user->name ?? '-' }}</div>
        </div>
        <div class="mt-3 flex flex-wrap gap-2">
          <a href="{{ route('ticket.show',$t->id) }}" class="rounded-lg bg-gradient-to-r from-blue-500 to-sky-500 px-3 py-2 text-tulisan-50 hover:bg-gray-800">Detail</a>
          @if($t->status==='ON_PROGRESS')
            <form method="POST" action="{{ route('it.ticket.release',$t->id) }}">@csrf
              <button class="rounded-lg bg-brand-700 px-3 py-1.5 text-tulisan-50 hover:bg-gray-300">Lepas</button>
            </form>
            <form method="POST" action="{{ route('it.ticket.close',$t->id) }}">@csrf
              <button class="rounded-lg bg-emerald-600 px-3 py-2 text-white hover:bg-emerald-700">Tutup</button>
            </form>
          @endif
        </div>
      </div>
    @empty
      <div class="text-center text-gray-500 py-8">Tidak ada tiket.</div>
    @endforelse
  </div>

  {{-- Pagination + summary (Showing kiri, paginate center, spacer kanan) --}}
  <div class="mt-4">
    <div class="grid grid-cols-1 md:grid-cols-3 items-center gap-3">
      {{-- LEFT: showing --}}
      <div class="text-sm text-gray-500 text-center md:text-left min-w-0">
        @if($tickets->total())
          Showing {{ $tickets->firstItem() }} to {{ $tickets->lastItem() }} of {{ $tickets->total() }} results
        @endif
      </div>

      {{-- CENTER: pagination --}}
      <div class="flex justify-center">
        {!! $tickets->appends(request()->except('page'))->links('pagination::tailwind') !!}
      </div>

      {{-- RIGHT: spacer --}}
      <div></div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const catSelect = document.getElementById('filter-category');
  const subSelect = document.getElementById('filter-subcategory');
  // endpoint base — pastikan route di web.php: /categories/{id}/subcategories
  const baseUrl = '{{ url('categories') }}';
  const csrf = '{{ csrf_token() }}';
  const initialCategory = '{{ request("category_id") }}';
  const initialSub = '{{ request("subcategory_id") }}';

  function emptySubOptions(text = 'Semua Subkategori') {
    subSelect.innerHTML = '';
    const d = document.createElement('option');
    d.value = '';
    d.textContent = text;
    subSelect.appendChild(d);
  }

  async function loadSubs(catId, selectValue = null) {
    emptySubOptions('Memuat...');
    if (!catId) {
      // jika tidak ada category, reset ke default
      emptySubOptions('Semua Subkategori');
      return;
    }

    try {
      const url = `${baseUrl}/${encodeURIComponent(catId)}/subcategories`;
      const res = await fetch(url, {
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrf,
          'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
      });

      if (!res.ok) {
        console.warn('Gagal memuat subkategori, status', res.status);
        // jika 401/302 kemungkinan sesi logout -> biarkan pesan
        emptySubOptions('— Gagal memuat —');
        return;
      }

      const data = await res.json();

      // support API yang mengembalikan { data: [...] } atau langsung array
      const list = Array.isArray(data) ? data : (Array.isArray(data.data) ? data.data : []);
      if (!list.length) {
        emptySubOptions('— Tidak ada subkategori —');
        return;
      }

      // populate options
      subSelect.innerHTML = '';
      const defaultOpt = document.createElement('option');
      defaultOpt.value = '';
      defaultOpt.textContent = 'Semua Subkategori';
      subSelect.appendChild(defaultOpt);

      list.forEach(s => {
        const opt = document.createElement('option');
        // jika object lengkap: gunakan s.id dan s.name, jika string: pakai s
        opt.value = (s.id !== undefined) ? s.id : (s.value ?? s);
        opt.textContent = (s.name !== undefined) ? s.name : (s.label ?? s);
        subSelect.appendChild(opt);
      });

      // set selected jika ada
      if (selectValue) {
        // coba set value, jika tidak ada, tetap kosong
        subSelect.value = selectValue;
      }
    } catch (err) {
      console.error('Error saat memuat subkategori', err);
      emptySubOptions('— Error memuat —');
    }
  }

  catSelect?.addEventListener('change', function () {
    loadSubs(this.value, null);
  });

  // load initial subcategories jika category preselected
  if (initialCategory) {
    loadSubs(initialCategory, initialSub);
  }
});
</script>


@endsection
