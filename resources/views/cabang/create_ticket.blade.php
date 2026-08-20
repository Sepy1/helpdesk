@extends('layouts.app')
@section('title','Buat Tiket')

@section('content')
<div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-3 sm:p-5 max-w-3xl mx-3 sm:mx-0 text-xs sm:text-sm">
  <h2 class="text-base sm:text-lg font-semibold text-gray-800 mb-4">Buat Tiket Helpdesk</h2>

  {{-- Error summary --}}
  @if($errors->any())
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 text-red-700 px-4 py-3">
      <ul class="list-disc list-inside space-y-1">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ auth()->user()->role === 'IT' ? route('cabang.ticket.store.it') : route('cabang.ticket.store') }}" enctype="multipart/form-data" class="space-y-3">
    @csrf

    {{-- Kategori --}}
   <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Kategori</label>
            <select name="category_id" id="category-select"
              required
              class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 py-1">
        <option value="">-</option>
        @php $list = $categories ?? collect(); @endphp
        @foreach($list as $cat)
          <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>{{ $cat->name }}</option>
        @endforeach
      </select>
      @error('category_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Subkategori (akan diisi via JS) --}}
    <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Subkategori</label>
            <select name="subcategory_id" id="subcategory-select"
              required
              class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 py-1">
        <option value="">-</option>
        {{-- Jika ada old value dan kategori terpilih, server-side create() bisa mengirim initial subkategori; 
            tapi kita handle juga via JS pada page load --}}
      </select>
      @error('subcategory_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Deskripsi --}}
    <div>
      <label class="block text-xs font-medium text-gray-700 mb-1">Deskripsi Permintaan</label>
      <textarea name="deskripsi" id="deskripsi" rows="3" required
            class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 h-20 sm:h-28 resize-none text-sm"
            placeholder="Jelaskan masalah/permintaan secara singkat dan jelas...">{{ old('deskripsi') }}</textarea>
      @error('deskripsi') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Lampiran --}}
    {{-- Lampiran + Assign TI --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">
      <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">Ditugaskan ke (TI) (opsional)</label>
        <select name="it_id" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 py-1 text-sm">
          <option value="">-</option>
          @php $itsList = $its ?? collect(); $itCounts = $itCounts ?? []; @endphp
          @foreach($itsList as $it)
            @php $count = $itCounts[$it->id] ?? 0; @endphp
            <option value="{{ $it->id }}" @selected(old('it_id') == $it->id)>{{ $it->name }} ( {{ $count }} Tiket )</option>
          @endforeach
        </select>
        @error('it_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Lampiran (opsional)</label>
        <div id="attachments" class="space-y-2">
          <div class="flex items-center gap-2">
            <input type="file" name="lampiran[]" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx"
                   class="block w-full text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-gray-900 file:px-3 file:py-2 file:text-white hover:file:bg-gray-800 rounded-lg border border-gray-300" />
            <button type="button" data-action="remove" class="text-sm text-red-600 hover:underline hidden">Hapus</button>
          </div>
        </div>
        <div class="mt-2 flex items-center gap-2">
          <button type="button" id="addAttachment" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 text-sm">Tambah lampiran</button>
          <div class="text-xs text-gray-500">(maks 3 file, 3 MB per file)</div>
        </div>
        @error('lampiran') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        @error('lampiran.*') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror

        <script>
          (function(){
            const max = 3;
            const container = document.getElementById('attachments');
            const addBtn = document.getElementById('addAttachment');

            function updateButtons(){
              const items = container.querySelectorAll('div > input[type=file]');
              const removeBtns = container.querySelectorAll('button[data-action="remove"]');
              items.forEach((_,i)=> removeBtns[i].classList.toggle('hidden', items.length<=1));
              addBtn.disabled = items.length >= max;
            }

            addBtn.addEventListener('click', ()=>{
              const count = container.querySelectorAll('input[type=file]').length;
              if(count >= max) return;
              const wrapper = document.createElement('div');
              wrapper.className = 'flex items-center gap-2';
              wrapper.innerHTML = ` <input type="file" name="lampiran[]" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx" class="block w-full text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-gray-900 file:px-3 file:py-2 file:text-white hover:file:bg-gray-800 rounded-lg border border-gray-300" /> <button type="button" data-action="remove" class="text-sm text-red-600 hover:underline">Hapus</button>`;
              container.appendChild(wrapper);
              wrapper.querySelector('button[data-action="remove"]').addEventListener('click', ()=>{ wrapper.remove(); updateButtons(); });
              updateButtons();
            });

            // attach remove handler for initial row
            container.querySelectorAll('button[data-action="remove"]').forEach(btn=>{
              btn.addEventListener('click', ()=>{ btn.closest('div').remove(); updateButtons(); });
            });

            // initial state
            updateButtons();
          })();
        </script>
      </div>
    </div>

    <div class="pt-2">
      <button type="submit" class="hd-btn-primary block w-full justify-center px-4 sm:px-8 py-2.5 text-sm font-medium shadow-sm">
        Kirim Tiket
      </button>
    </div>
  </form>
</div>

<div id="pergantian-user-modal" class="fixed inset-0 z-50 hidden items-center justify-center px-3">
  <div class="absolute inset-0 bg-black/50" data-close-pergantian-modal></div>
  <div class="relative z-10 w-full max-w-2xl rounded-2xl bg-white p-4 sm:p-5 shadow-2xl ring-1 ring-black/5">
    <div class="flex items-start justify-between gap-3 mb-4">
      <div>
        <h3 class="text-base sm:text-lg font-semibold text-gray-900">Pergantian User</h3>
        <p class="text-xs sm:text-sm text-gray-500">Lengkapi data pergantian user sebelum tiket dikirim.</p>
      </div>
      <button type="button" class="rounded-lg px-2 py-1 text-gray-500 hover:bg-gray-100" data-close-pergantian-modal>?</button>
    </div>

    <div class="space-y-3">
      <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">User Lama</label>
        <input type="hidden" name="user_lama_id" id="user-lama-id" value="{{ old('user_lama_id') }}">
        <input type="text" id="user-lama-search" list="user-lama-list" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 py-1 text-sm" placeholder="Ketik nama user lama..." value="{{ old('user_lama_name') }}">
        <datalist id="user-lama-list"></datalist>
        @error('user_lama_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">User Pengganti</label>
        <input type="hidden" name="user_pengganti_id" id="user-pengganti-id" value="{{ old('user_pengganti_id') }}">
        <input type="text" id="user-pengganti-search" list="user-pengganti-list" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 py-1 text-sm" placeholder="Ketik nama user pengganti..." value="{{ old('user_pengganti_name') }}">
        <datalist id="user-pengganti-list"></datalist>
        @error('user_pengganti_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Tanggal Awal</label>
          <input type="date" name="tanggal_awal" id="tanggal-awal" value="{{ old('tanggal_awal') }}" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 py-1 text-sm">
          @error('tanggal_awal') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Tanggal Selesai</label>
          <input type="date" name="tanggal_selesai" id="tanggal-selesai" value="{{ old('tanggal_selesai') }}" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 py-1 text-sm">
          @error('tanggal_selesai') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">Alasan</label>
        <textarea name="alasan_pergantian" id="alasan-pergantian" rows="3" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 py-1 text-sm" placeholder="Jelaskan alasan pergantian user...">{{ old('alasan_pergantian') }}</textarea>
        @error('alasan_pergantian') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>
    </div>

    <div class="mt-5 flex items-center justify-end gap-2">
      <button type="button" class="rounded-lg bg-gray-100 px-4 py-2 text-sm text-gray-700 hover:bg-gray-200" data-close-pergantian-modal>Batal</button>
      <button type="button" id="pergantian-user-done" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Simpan</button>
    </div>
  </div>
</div>

{{-- Modal sukses buat tiket --}}
@if(session('new_ticket_no'))
<div x-data="{ open: true }" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
  <div class="absolute inset-0 bg-black/40" @click="open=false"></div>

  <div class="relative bg-white w-full max-w-md mx-auto rounded-2xl shadow-xl p-3 sm:p-5 text-xs sm:text-sm">
    <div class="flex items-start gap-3">
      <div class="h-10 w-10 rounded-full bg-emerald-100 flex items-center justify-center">✅</div>
      <div class="flex-1">
        <h3 class="text-lg font-semibold text-gray-800">Tiket Berhasil Dibuat</h3>
        <p class="text-sm text-gray-600 mt-1">
          Simpan dan <span class="font-medium text-gray-800">tunjukkan nomor tiket ini</span> ke tim TI untuk percepatan penanganan.
        </p>

        <div class="mt-3 flex items-center gap-2">
          <code id="ticketNo"
                class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-900 font-semibold">
            {{ session('new_ticket_no') }}
          </code>
          <button type="button"
                  class="text-sm px-3 py-1.5 rounded-lg bg-gray-900 text-white hover:bg-gray-800"
                  onclick="navigator.clipboard.writeText(document.getElementById('ticketNo').innerText)">
            Salin
          </button>
        </div>
      </div>
    </div>

    <div class="mt-5 flex gap-2 justify-end">
      <a href="{{ route('ticket.show', session('new_ticket_id')) }}"
         class="inline-flex items-center px-3 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">
        Lihat Detail Tiket
      </a>
      <button @click="open=false"
              class="inline-flex items-center px-3 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200">
        Tutup
      </button>
    </div>
  </div>
</div>
@endif



<script>
document.addEventListener('DOMContentLoaded', function () {
  const categorySelect = document.getElementById('category-select');
  const subcategorySelect = document.getElementById('subcategory-select');
  const pergantianFields = document.getElementById('pergantian-user-fields');
  const pergantianModal = document.getElementById('pergantian-user-modal');
  const pergantianDoneBtn = document.getElementById('pergantian-user-done');
  const pergantianCloseButtons = document.querySelectorAll('[data-close-pergantian-modal]');
  const userLamaSearch = document.getElementById('user-lama-search');
  const userLamaId = document.getElementById('user-lama-id');
  const userLamaList = document.getElementById('user-lama-list');
  const userPenggantiSearch = document.getElementById('user-pengganti-search');
  const userPenggantiId = document.getElementById('user-pengganti-id');
  const userPenggantiList = document.getElementById('user-pengganti-list');
  const tanggalAwal = document.getElementById('tanggal-awal');
  const tanggalSelesai = document.getElementById('tanggal-selesai');
  const alasanPergantian = document.getElementById('alasan-pergantian');
  const deskripsi = document.getElementById('deskripsi');

  const baseUrl = '{{ url('/categories') }}'; // -> /categories
  const pergantianUrl = '{{ route('pergantian-users.index') }}';
  const csrfToken = '{{ csrf_token() }}';
  const oldCategory = '{{ old("category_id") }}';
  const oldSub = '{{ old("subcategory_id") }}';
  const oldUserLama = '{{ old("user_lama_id") }}';
  const oldUserPengganti = '{{ old("user_pengganti_id") }}';
  const oldDeskripsi = @json(old('deskripsi'));

  const unitKerja = @json($reporterUnit ?? null);
  const pergantianUsers = @json(($pergantianUsersByUnit ?? collect())->values());
  function renderDatalist(listEl, list) {
    listEl.innerHTML = list
      .slice(0, 5)
      .map((item) => `<option value="${item.nama_lengkap} (${item.user_name})"></option>`)
      .join('');
  }

  function filterPergantianUsers(keyword) {
    const q = (keyword || '').trim().toLowerCase();
    if (!q) {
      return [...pergantianUsers].slice(0, 5);
    }
    return pergantianUsers
      .filter((item) => {
        return String(item.user_name || '').toLowerCase().includes(q)
          || String(item.nama_lengkap || '').toLowerCase().includes(q);
      })
      .slice(0, 5);
  }

  function syncSuggestions() {
    const filteredUserLama = filterPergantianUsers(userLamaSearch.value);
    const filteredUserPengganti = filterPergantianUsers(userPenggantiSearch.value);
    renderDatalist(userLamaList, filteredUserLama);
    renderDatalist(userPenggantiList, filteredUserPengganti);
  }

  function syncHiddenId(searchEl, hiddenEl) {
    const value = (searchEl.value || '').trim().toLowerCase();
    const match = pergantianUsers.find((item) => {
      const label = `${item.nama_lengkap} (${item.user_name})`.toLowerCase();
      return label === value || item.nama_lengkap.toLowerCase() === value || item.user_name.toLowerCase() === value;
    });
    hiddenEl.value = match ? match.id : '';
  }

  function updateHiddenAndDescription() {
    syncHiddenId(userLamaSearch, userLamaId);
    syncHiddenId(userPenggantiSearch, userPenggantiId);
    syncDeskripsi();
  }

  function validatePergantianModal() {
    const fields = [
      userLamaSearch,
      userPenggantiSearch,
      tanggalAwal,
      tanggalSelesai,
      alasanPergantian,
    ];

    for (const field of fields) {
      if (!field.value || !field.value.trim()) {
        field.reportValidity();
        field.focus();
        return false;
      }
    }

    if (!userLamaId.value || !userPenggantiId.value) {
      const target = !userLamaId.value ? userLamaSearch : userPenggantiSearch;
      target.setCustomValidity('Silakan pilih user dari daftar suggestion.');
      target.reportValidity();
      target.setCustomValidity('');
      target.focus();
      return false;
    }

    return true;
  }

  function resetPergantianUserState() {
    userLamaSearch.value = '';
    userLamaId.value = '';
    userPenggantiSearch.value = '';
    userPenggantiId.value = '';
    tanggalAwal.value = '';
    tanggalSelesai.value = '';
    alasanPergantian.value = '';
    deskripsi.value = '';
    userLamaList.innerHTML = '';
    userPenggantiList.innerHTML = '';
  }

  function openPergantianModal() {
    pergantianModal.classList.remove('hidden');
    pergantianModal.classList.add('flex');
  }

  function closePergantianModal({ reset = true } = {}) {
    pergantianModal.classList.add('hidden');
    pergantianModal.classList.remove('flex');
    if (reset) {
      resetPergantianUserState();
      subcategorySelect.value = '';
    }
  }

  async function loadSubcategories(categoryId, setSelected = null) {
    // reset first
    subcategorySelect.innerHTML = '<option value="">-- Pilih Subkategori --</option>';

    if (!categoryId) {
      // nothing to load
      return;
    }

    const url = `${baseUrl}/${categoryId}/subcategories`;

    try {
      const res = await fetch(url, {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': csrfToken
        }
      });

      if (!res.ok) {
        console.error('Gagal memuat subkategori', res.status);
        return;
      }

      const data = await res.json();

      if (!Array.isArray(data) || data.length === 0) {
        const opt = document.createElement('option');
        opt.value = '';
        opt.textContent = '— Tidak ada subkategori —';
        subcategorySelect.appendChild(opt);
        return;
      }

      data.forEach(s => {
        const opt = document.createElement('option');
        opt.value = s.id;
        opt.textContent = s.name;
        subcategorySelect.appendChild(opt);
      });

      // set selected jika ada
      const toSelect = setSelected ?? oldSub;
      if (toSelect) subcategorySelect.value = toSelect;
      updatePergantianUserFields();
    } catch (err) {
      console.error('Error saat memuat subkategori', err);
    }
  }

  async function loadPergantianUsers(setOld = null, setPengganti = null) {
    syncSuggestions();
    if (setOld) {
      userLamaId.value = setOld;
    }
    if (setPengganti) {
      userPenggantiId.value = setPengganti;
    }
  }

  function getSelectedSubcategoryText() {
    return (subcategorySelect.selectedOptions[0]?.textContent || '').trim().toLowerCase();
  }

  function isPergantianUserSelected() {
    return getSelectedSubcategoryText() === 'pergantian user';
  }

  function syncDeskripsi() {
    if (!isPergantianUserSelected()) return;
    const lamaItem = pergantianUsers.find((item) => String(item.id) === String(userLamaId.value));
    const penggantiItem = pergantianUsers.find((item) => String(item.id) === String(userPenggantiId.value));
    if (!lamaItem || !penggantiItem) return;
    const awal = tanggalAwal.value ? tanggalAwal.value : '-';
    const selesai = tanggalSelesai.value ? tanggalSelesai.value : '-';
    const alasan = (alasanPergantian.value || '').trim() || '-';
    const userLama = lamaItem ? lamaItem.user_name : '-';
    const userPengganti = penggantiItem ? penggantiItem.user_name : '-';
    deskripsi.value = `Permohonan pergantian user dengan detail sebagai berikut :
user lama : ${userLama}
user pengganti : ${userPengganti}
Periode : ${awal} - ${selesai}
Alasan : ${alasan}`;
  }

  function updatePergantianUserFields() {
    const show = isPergantianUserSelected();
    if (show) {
      loadPergantianUsers(oldUserLama, oldUserPengganti).then(() => {
        syncDeskripsi();
        openPergantianModal();
      });
    } else {
      closePergantianModal();
    }
  }

  // Event listener saat kategori berubah
  categorySelect.addEventListener('change', function () {
    const catId = this.value;
    loadSubcategories(catId, null);
  });

  subcategorySelect.addEventListener('change', function () {
    updatePergantianUserFields();
  });

  userLamaSearch.addEventListener('input', function () {
    syncSuggestions();
    updateHiddenAndDescription();
  });
  userLamaSearch.addEventListener('change', function () {
    updateHiddenAndDescription();
  });

  userPenggantiSearch.addEventListener('input', function () {
    syncSuggestions();
    updateHiddenAndDescription();
  });
  userPenggantiSearch.addEventListener('change', function () {
    updateHiddenAndDescription();
  });

  tanggalAwal.addEventListener('change', updateHiddenAndDescription);
  tanggalSelesai.addEventListener('change', updateHiddenAndDescription);
  alasanPergantian.addEventListener('input', updateHiddenAndDescription);

  pergantianDoneBtn.addEventListener('click', function () {
    if (!validatePergantianModal()) {
      return;
    }
    updateHiddenAndDescription();
    closePergantianModal({ reset: false });
  });

  pergantianCloseButtons.forEach((button) => {
    button.addEventListener('click', closePergantianModal);
  });

  tanggalAwal.addEventListener('change', updateHiddenAndDescription);
  tanggalSelesai.addEventListener('change', updateHiddenAndDescription);
  alasanPergantian.addEventListener('input', updateHiddenAndDescription);

  // Jika ada old value (mis. after validation error), muat subkategori pada page load
  if (oldCategory) {
    // set select to old category (already set by blade) then load subcategories and set old sub
    loadSubcategories(oldCategory, oldSub);
  }

  updatePergantianUserFields();

  if (oldUserLama) {
    const selected = pergantianUsers.find((item) => String(item.id) === String(oldUserLama));
    if (selected) {
      userLamaSearch.value = `${selected.nama_lengkap} (${selected.user_name})`;
      userLamaId.value = selected.id;
    }
  }
  if (oldUserPengganti) {
    const selected = pergantianUsers.find((item) => String(item.id) === String(oldUserPengganti));
    if (selected) {
      userPenggantiSearch.value = `${selected.nama_lengkap} (${selected.user_name})`;
      userPenggantiId.value = selected.id;
    }
  }

  updateHiddenAndDescription();

  if (oldDeskripsi && !oldDeskripsi.startsWith('Permohonan pergantian user dengan detail sebagai berikut :')) {
    deskripsi.value = oldDeskripsi;
  }

  if (isPergantianUserSelected()) {
    openPergantianModal();
  }
});
</script>

@endsection
