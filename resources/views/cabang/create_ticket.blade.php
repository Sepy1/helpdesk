@extends('layouts.app')
@section('title','Buat Tiket')

@section('content')
<div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6 max-w-3xl">
  <h2 class="text-lg font-semibold text-gray-800 mb-6">Buat Tiket Helpdesk</h2>

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

  <form method="POST" action="{{ route('cabang.ticket.store') }}" enctype="multipart/form-data" class="space-y-5">
    @csrf

    {{-- Kategori --}}
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
      <select name="kategori"
              required
              class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
        <option value="">-- Pilih --</option>
        @php $listKategori = $kategori ?? ['JARINGAN','LAYANAN','CBS','OTHER']; @endphp
        @foreach($listKategori as $k)
          <option value="{{ $k }}" @selected(old('kategori')===$k)>{{ ucwords(strtolower($k)) }}</option>
        @endforeach
      </select>
      @error('kategori') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Deskripsi --}}
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Permintaan</label>
      <textarea name="deskripsi" rows="5" required
                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                placeholder="Jelaskan masalah/permintaan secara singkat dan jelas...">{{ old('deskripsi') }}</textarea>
      @error('deskripsi') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Lampiran --}}
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Lampiran (opsional)</label>
      <input type="file" name="lampiran"
             class="block w-full text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-2 file:text-white hover:file:bg-gray-800 rounded-lg border border-gray-300"
      />
      <p class="text-xs text-gray-500 mt-1">jpg, jpeg, png, pdf, doc, docx (maks 3 MB)</p>
      @error('lampiran') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="pt-2">
      <button class="block w-full items-center rounded-lg bg-brand-50 px-10 py-2 text-white hover:bg-indigo-700">
        Kirim Tiket
      </button>
    </div>
  </form>
</div>

{{-- Modal sukses buat tiket --}}
@if(session('new_ticket_no'))
<div x-data="{ open: true }" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
  <div class="absolute inset-0 bg-black/40" @click="open=false"></div>

  <div class="relative bg-white w-full max-w-md mx-auto rounded-2xl shadow-xl p-6">
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
@endsection
