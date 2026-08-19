@extends('layouts.app')
@section('title','Manajemen Popup')

@section('content')
@php
  $input = 'mt-1 h-10 w-full rounded-lg border-slate-200 bg-white text-sm text-slate-800 shadow-sm focus:border-violet-500 focus:ring-violet-500';
  $label = 'text-xs font-semibold uppercase tracking-wide text-slate-500';
@endphp

<div
  class="w-full max-w-none pb-8"
  x-data="{ previewOpen: false, preview: { title: '', image: '', description: '' } }"
>
  @if(session('success'))
    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">{{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-700">
      <ul class="list-disc pl-5 space-y-1">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="mb-4">
    <div class="flex flex-wrap items-center gap-2 text-xs font-medium text-slate-500">
      <span class="rounded-md border border-blue-100 bg-blue-50 px-2 py-1 text-blue-700">IT Popup</span>
      <span>{{ now()->format('d/m/Y H:i') }}</span>
    </div>
    <h1 class="mt-2 text-2xl font-semibold tracking-normal text-slate-900 sm:text-3xl">Manajemen Popup Informasi</h1>
    <p class="mt-1 max-w-3xl text-sm text-slate-500">Tambahkan gambar informasi yang akan muncul otomatis setelah user login.</p>
  </div>

  <div class="grid grid-cols-1 gap-4 xl:grid-cols-[24rem_minmax(0,1fr)]">
    <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
      <div class="border-b border-slate-100 px-4 py-3 sm:px-5">
        <h2 class="text-base font-semibold text-slate-900">Tambah Popup</h2>
        <p class="mt-1 text-xs leading-5 text-slate-500">Popup ini akan tampil saat user login selama statusnya aktif.</p>
      </div>
      <div class="p-4 sm:p-5">
        <form method="POST" action="{{ route('it.popups.store') }}" enctype="multipart/form-data" class="mt-4 space-y-4">
          @csrf
          <div>
            <label class="{{ $label }}">Judul</label>
            <input type="text" name="title" class="{{ $input }}" value="{{ old('title') }}" required>
          </div>
          <div>
            <label class="{{ $label }}">Deskripsi</label>
            <textarea name="description" rows="4" class="mt-1 w-full rounded-lg border-slate-200 bg-white text-sm text-slate-800 shadow-sm focus:border-violet-500 focus:ring-violet-500">{{ old('description') }}</textarea>
          </div>
          <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div>
              <label class="{{ $label }}">Mulai</label>
              <input type="datetime-local" name="starts_at" class="{{ $input }}" value="{{ old('starts_at') }}">
            </div>
            <div>
              <label class="{{ $label }}">Berakhir</label>
              <input type="datetime-local" name="ends_at" class="{{ $input }}" value="{{ old('ends_at') }}">
            </div>
          </div>
          <div>
            <label class="{{ $label }}">Gambar</label>
            <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp" class="mt-1 block w-full text-sm text-slate-700">
          </div>
          <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div>
              <label class="{{ $label }}">Urutan</label>
              <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" class="{{ $input }}">
            </div>
            <label class="flex items-center gap-2 pt-6 text-sm text-slate-700">
              <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300 text-violet-600 focus:ring-violet-500">
              Aktif
            </label>
          </div>
          <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <label class="flex items-center gap-2 text-sm text-slate-700">
              <input type="checkbox" name="desktop_active" value="1" checked class="rounded border-slate-300 text-violet-600 focus:ring-violet-500">
              Aktif di Desktop
            </label>
            <label class="flex items-center gap-2 text-sm text-slate-700">
              <input type="checkbox" name="mobile_active" value="1" checked class="rounded border-slate-300 text-violet-600 focus:ring-violet-500">
              Aktif di Mobile
            </label>
          </div>
          <button class="inline-flex h-10 items-center justify-center rounded-lg bg-violet-600 px-4 text-sm font-medium text-white hover:bg-violet-700">Simpan Popup</button>
        </form>
      </div>
    </div>

    <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
      <div class="border-b border-slate-100 px-4 py-3 sm:px-5 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-slate-900">Daftar Popup</h2>
        <span class="text-sm text-slate-500">{{ $popups->count() }} data</span>
      </div>

      <div class="p-4 sm:p-5 space-y-4">
        @forelse($popups as $popup)
          <div class="rounded-2xl border border-slate-200 p-4">
            <div class="flex flex-col gap-4 md:flex-row">
              @if($popup->image_path)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($popup->image_path) }}" class="h-36 w-full rounded-xl object-cover md:w-48" alt="{{ $popup->title }}">
              @endif
              <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                  <h3 class="text-base font-semibold text-slate-900">{{ $popup->title }}</h3>
                  <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $popup->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                    {{ $popup->is_active ? 'Aktif' : 'Nonaktif' }}
                  </span>
                  <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $popup->desktop_active ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-500' }}">
                    {{ $popup->desktop_active ? 'Desktop Aktif' : 'Desktop Nonaktif' }}
                  </span>
                  <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $popup->mobile_active ? 'bg-violet-100 text-violet-700' : 'bg-slate-100 text-slate-500' }}">
                    {{ $popup->mobile_active ? 'Mobile Aktif' : 'Mobile Nonaktif' }}
                  </span>
                </div>
                @if($popup->description)
                  <p class="mt-2 text-sm text-slate-600 whitespace-pre-line">{{ $popup->description }}</p>
                @endif
                <p class="mt-2 text-xs text-slate-500">
                  Urutan: {{ $popup->sort_order }} |
                  Mulai: {{ optional($popup->starts_at)->format('d M Y H:i') ?? '-' }} |
                  Berakhir: {{ optional($popup->ends_at)->format('d M Y H:i') ?? '-' }}
                </p>

                <div class="mt-4 flex flex-wrap gap-2">
                  <button
                    type="button"
                    class="rounded-lg border border-violet-200 px-3 py-2 text-sm font-medium text-violet-700 hover:bg-violet-50"
                    @click="preview = { title: @js($popup->title), image: @js($popup->image_path ? \Illuminate\Support\Facades\Storage::url($popup->image_path) : ''), description: @js($popup->description ?? '') }; previewOpen = true"
                  >
                    Preview
                  </button>

                  <form method="POST" action="{{ route('it.popups.update', $popup) }}" enctype="multipart/form-data" class="flex flex-wrap gap-2">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="title" value="{{ $popup->title }}">
                    <input type="hidden" name="description" value="{{ $popup->description }}">
                    <input type="hidden" name="sort_order" value="{{ $popup->sort_order }}">
                    <input type="hidden" name="starts_at" value="{{ optional($popup->starts_at)->format('Y-m-d\TH:i') }}">
                    <input type="hidden" name="ends_at" value="{{ optional($popup->ends_at)->format('Y-m-d\TH:i') }}">
                    <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm">
                      <input type="checkbox" name="is_active" value="1" {{ $popup->is_active ? 'checked' : '' }}>
                      {{ $popup->is_active ? 'Matikan' : 'Aktifkan' }}
                    </label>
                    <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm">
                      <input type="checkbox" name="desktop_active" value="1" {{ $popup->desktop_active ? 'checked' : '' }}>
                      {{ $popup->desktop_active ? 'Desktop Matikan' : 'Desktop Aktifkan' }}
                    </label>
                    <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm">
                      <input type="checkbox" name="mobile_active" value="1" {{ $popup->mobile_active ? 'checked' : '' }}>
                      {{ $popup->mobile_active ? 'Mobile Matikan' : 'Mobile Aktifkan' }}
                    </label>
                    <button class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-800">Simpan Status</button>
                  </form>

                  <form method="POST" action="{{ route('it.popups.destroy', $popup) }}" onsubmit="return confirm('Hapus popup ini?');">
                    @csrf
                    @method('DELETE')
                    <button class="rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50">Hapus</button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        @empty
          <div class="rounded-xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500">
            Belum ada popup.
          </div>
        @endforelse
      </div>

      <div x-cloak x-show="previewOpen" class="fixed inset-0 z-[120] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/75 backdrop-blur-sm" @click="previewOpen = false"></div>
        <div class="relative w-full max-w-3xl overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-black/10">
          <div class="flex items-center justify-end px-4 py-3 sm:px-5">
            <button type="button" class="rounded-full p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700" @click="previewOpen = false" aria-label="Tutup preview">×</button>
          </div>
          <template x-if="preview.image">
            <div class="bg-slate-950">
              <img :src="preview.image" :alt="preview.title" class="max-h-[70vh] w-full object-contain">
            </div>
          </template>
          <div class="px-5 py-4 sm:px-6">
            <p class="text-base font-semibold text-slate-900" x-text="preview.title"></p>
            <p class="mt-2 text-sm text-slate-600 whitespace-pre-line" x-text="preview.description || '-'"></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
