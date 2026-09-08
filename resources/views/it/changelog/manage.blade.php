@extends('layouts.app')
@section('title', 'Manajemen Changelog')

@section('content')
@php $formEntry = $editing ?? null; @endphp
<div class="space-y-5 pb-8">
  <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Manajemen Changelog</h1>
      <p class="mt-1 text-sm text-slate-500">Kelola catatan pembaruan aplikasi untuk kebutuhan internal IT.</p>
    </div>
    <a href="{{ route('it.changelog.index') }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-blue-200 bg-blue-50 px-4 text-sm font-semibold text-blue-700 transition hover:bg-blue-100">Lihat Changelog</a>
  </header>

  @if(session('success'))<div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>@endif
  @if($errors->any())<div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

  <div class="grid grid-cols-1 gap-5 xl:grid-cols-[minmax(22rem,0.8fr)_minmax(0,1.2fr)]">
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
      <h2 class="text-lg font-bold text-slate-900">{{ $formEntry ? 'Edit Changelog' : 'Tambah Changelog' }}</h2>
      <form method="POST" action="{{ $formEntry ? route('it.changelog.update', $formEntry) : route('it.changelog.store') }}" class="mt-4 space-y-4">
        @csrf
        @if($formEntry) @method('PUT') @endif
        <div class="grid grid-cols-2 gap-3">
          <div><label class="mb-1 block text-xs font-semibold text-slate-600">Versi</label><input name="version" value="{{ old('version', $formEntry?->version) }}" class="h-10 w-full rounded-lg border-slate-200 text-sm" placeholder="Contoh: 1.5.0"></div>
          <div><label class="mb-1 block text-xs font-semibold text-slate-600">Tanggal Rilis</label><input type="date" name="release_date" required value="{{ old('release_date', $formEntry?->release_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" class="h-10 w-full rounded-lg border-slate-200 text-sm"></div>
        </div>
        <div><label class="mb-1 block text-xs font-semibold text-slate-600">Jenis</label><select name="type" required class="h-10 w-full rounded-lg border-slate-200 text-sm">@foreach(['added'=>'Fitur Baru','changed'=>'Perubahan','fixed'=>'Perbaikan','security'=>'Keamanan'] as $value=>$label)<option value="{{ $value }}" @selected(old('type', $formEntry?->type ?? 'changed') === $value)>{{ $label }}</option>@endforeach</select></div>
        <div><label class="mb-1 block text-xs font-semibold text-slate-600">Judul</label><input name="title" required maxlength="255" value="{{ old('title', $formEntry?->title) }}" class="h-10 w-full rounded-lg border-slate-200 text-sm" placeholder="Judul pembaruan"></div>
        <div><label class="mb-1 block text-xs font-semibold text-slate-600">Ringkasan</label><textarea name="summary" rows="2" maxlength="1000" class="w-full rounded-lg border-slate-200 text-sm" placeholder="Ringkasan singkat">{{ old('summary', $formEntry?->summary) }}</textarea></div>
        <div><label class="mb-1 block text-xs font-semibold text-slate-600">Detail Perubahan</label><textarea name="details" rows="9" required class="w-full rounded-lg border-slate-200 text-sm" required placeholder="Tuliskan setiap perubahan pada baris terpisah...">{{ old('details', $formEntry?->details) }}</textarea></div>
        <label class="flex items-center gap-2 text-sm text-slate-700"><input type="checkbox" name="is_published" value="1" class="rounded border-slate-300 text-blue-600" @checked(old('is_published', $formEntry?->is_published ?? true))> Publikasikan ke seluruh user</label>
        <div class="flex gap-2"><button class="hd-btn-primary inline-flex h-10 items-center rounded-lg px-4 text-sm font-semibold text-white">{{ $formEntry ? 'Simpan Perubahan' : 'Tambah Changelog' }}</button>@if($formEntry)<a href="{{ route('it.changelog.manage') }}" class="inline-flex h-10 items-center rounded-lg border border-slate-200 px-4 text-sm">Batal</a>@endif</div>
      </form>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
      <div class="border-b border-slate-100 px-5 py-4"><h2 class="text-lg font-bold text-slate-900">Daftar Changelog</h2><p class="text-xs text-slate-500">{{ $entries->total() }} entri</p></div>
      <div class="divide-y divide-slate-100">
        @forelse($entries as $entry)
          <article class="p-4 sm:p-5">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0"><div class="flex flex-wrap items-center gap-2"><span class="font-mono text-xs font-bold">{{ $entry->version ? 'v'.$entry->version : 'Tanpa versi' }}</span><span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $entry->is_published ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $entry->is_published ? 'Published' : 'Draft' }}</span></div><h3 class="mt-1 truncate font-semibold text-slate-900">{{ $entry->title }}</h3><p class="mt-1 line-clamp-2 text-xs text-slate-500">{{ $entry->summary ?: $entry->details }}</p><p class="mt-2 text-[11px] text-slate-400">{{ $entry->release_date->format('d/m/Y') }} · {{ $entry->author->name ?? '-' }}</p></div>
              <div class="flex shrink-0 gap-1"><a href="{{ route('it.changelog.manage', ['edit'=>$entry->id]) }}" class="rounded-md bg-blue-50 px-2.5 py-1.5 text-xs font-semibold text-blue-700">Edit</a><form method="POST" action="{{ route('it.changelog.destroy', $entry) }}" onsubmit="return confirm('Hapus changelog ini?')">@csrf @method('DELETE')<button class="rounded-md bg-red-50 px-2.5 py-1.5 text-xs font-semibold text-red-700">Hapus</button></form></div>
            </div>
          </article>
        @empty
          <div class="px-5 py-12 text-center text-sm text-slate-500">Belum ada changelog.</div>
        @endforelse
      </div>
      <div class="border-t border-slate-100 p-4">{{ $entries->links() }}</div>
    </section>
  </div>
</div>
@endsection
