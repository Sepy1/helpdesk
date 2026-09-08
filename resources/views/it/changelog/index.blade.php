@extends('layouts.app')
@section('title', 'Daftar Changelog APP')

@section('content')
@php
  $typeStyles = [
    'added' => ['Fitur Baru', 'bg-emerald-100 text-emerald-700 border-emerald-200'],
    'changed' => ['Perubahan', 'bg-blue-100 text-blue-700 border-blue-200'],
    'fixed' => ['Perbaikan', 'bg-amber-100 text-amber-700 border-amber-200'],
    'security' => ['Keamanan', 'bg-red-100 text-red-700 border-red-200'],
  ];
@endphp

<div class="space-y-5 pb-8">
  <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Daftar Changelog APP</h1>
      <p class="mt-1 text-sm text-slate-500">Riwayat lengkap perubahan aplikasi, termasuk entri draft dan terpublikasi.</p>
    </div>
    <a href="{{ route('it.changelog.manage') }}" class="hd-btn-primary inline-flex h-10 items-center justify-center rounded-lg px-4 text-sm font-semibold text-white">Kelola Changelog</a>
  </header>

  <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-5 py-4">
      <p class="text-sm font-semibold text-slate-700">{{ $entries->total() }} changelog</p>
    </div>

    <div class="divide-y divide-slate-100">
      @forelse($entries as $entry)
        @php $type = $typeStyles[$entry->type] ?? ['Perubahan', 'bg-slate-100 text-slate-700 border-slate-200']; @endphp
        <article class="p-5 sm:p-6">
          <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
              <div class="flex flex-wrap items-center gap-2">
                <span class="font-mono text-sm font-bold text-slate-800">{{ $entry->version ? 'v'.$entry->version : 'Tanpa versi' }}</span>
                <span class="rounded-full border px-2.5 py-1 text-[11px] font-semibold {{ $type[1] }}">{{ $type[0] }}</span>
                <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $entry->is_published ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $entry->is_published ? 'Published' : 'Draft' }}</span>
              </div>
              <h2 class="mt-3 text-lg font-bold text-slate-900">{{ $entry->title }}</h2>
              @if($entry->summary)
                <p class="mt-2 text-sm leading-6 text-slate-600">{{ $entry->summary }}</p>
              @endif
            </div>
            <div class="shrink-0 text-xs text-slate-500 sm:text-right">
              <p class="font-semibold text-slate-700">{{ $entry->release_date->format('d M Y') }}</p>
              <p class="mt-1">{{ $entry->author->name ?? '-' }}</p>
            </div>
          </div>

          <div class="mt-4 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-700">
            {!! nl2br(e($entry->details)) !!}
          </div>
        </article>
      @empty
        <div class="px-5 py-16 text-center text-sm text-slate-500">Belum ada changelog.</div>
      @endforelse
    </div>

    @if($entries->hasPages())
      <div class="border-t border-slate-100 p-4">{{ $entries->links() }}</div>
    @endif
  </section>
</div>
@endsection
