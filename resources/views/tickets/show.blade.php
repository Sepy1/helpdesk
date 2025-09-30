@extends('layouts.app')
@section('title', 'Detail Tiket')

@section('content')
<div class="grid gap-6 lg:grid-cols-3">
  {{-- Kolom kiri: detail & komentar --}}
  <div class="lg:col-span-2 space-y-6">

    {{-- Kartu Detail --}}
    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h2 class="text-lg font-semibold text-gray-800">#{{ $ticket->nomor_tiket }}</h2>
          <p class="text-sm text-gray-500">
            Dibuat oleh: <span class="font-medium text-gray-700">{{ $ticket->user->name ?? '-' }}</span>
            • {{ $ticket->created_at->format('d M Y H:i') }}
          </p>
        </div>

        @php
          $badge = match($ticket->status) {
            'OPEN'        => 'bg-gray-100 text-gray-700 ring-gray-200',
            'ON_PROGRESS' => 'bg-amber-100 text-amber-800 ring-amber-200',
            'CLOSED'      => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
          };
        @endphp
        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 {{ $badge }}">
          {{ $ticket->status }}
        </span>
      </div>

      <div class="mt-6 grid sm:grid-cols-2 gap-4">
        <div>
          <div class="text-xs text-gray-500 mb-1">Kategori</div>
          <div class="font-medium">{{ $ticket->kategori }}</div>
        </div>
        <div>
          <div class="text-xs text-gray-500 mb-1">IT Handler</div>
          <div class="font-medium">{{ $ticket->it->name ?? '-' }}</div>
        </div>
      </div>

      <div class="mt-6">
        <div class="text-xs text-gray-500 mb-1">Deskripsi</div>
        <div class="whitespace-pre-line text-gray-800">{{ $ticket->deskripsi }}</div>
      </div>

      <div class="mt-4">
        <div class="text-xs text-gray-500 mb-1">Lampiran</div>
        @if($ticket->lampiran)
          <a class="text-indigo-600 hover:underline" href="{{ route('ticket.download', $ticket->id) }}">
            Unduh lampiran
          </a>
        @else
          <span class="text-gray-400">-</span>
        @endif
      </div>

      {{-- Aksi IT + History --}}
      <div class="mt-6 flex flex-wrap gap-2 items-center">
        @auth
          @if(auth()->user()->role === 'IT')
            {{-- Form Eskalasi (Vendor/Tidak) --}}
            <form method="POST" action="{{ route('it.ticket.eskalasi', $ticket->id) }}"
                  class="flex items-center gap-2">
              @csrf
              <label class="text-sm text-gray-600">Eskalasi</label>
              <select name="eskalasi"
                      class="rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                <option value="TIDAK"  @selected($ticket->eskalasi==='TIDAK')>Tidak</option>
                <option value="VENDOR" @selected($ticket->eskalasi==='VENDOR')>Vendor</option>
              </select>
              <button class="rounded-lg bg-gray-900 px-3 py-2 text-white hover:bg-gray-800 text-sm">
                Simpan
              </button>
            </form>
          @endif
        @endauth

        {{-- Tombol History (semua role bisa) --}}
        <button type="button"
                x-data
                @click="$dispatch('open-history')"
                class="rounded-lg bg-white ring-1 ring-gray-200 px-3 py-2 text-gray-700 hover:bg-gray-50">
          History
        </button>
      </div>
    </div>

    {{-- Kartu Komentar / Progres --}}
    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
      <h3 class="font-semibold text-gray-800 mb-3">Komentar / Progres</h3>

      {{-- Form komentar --}}
      <form method="POST" action="{{ route('ticket.comment', $ticket->id) }}" class="mb-4">
        @csrf
        <textarea name="body" rows="3" required
                  class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                  placeholder="Tulis update atau komentar..."></textarea>
        <div class="mt-2">
          <button class="rounded-lg bg-gray-900 px-3 py-2 text-white hover:bg-gray-800">Kirim</button>
        </div>
      </form>

      {{-- List komentar --}}
      <div class="space-y-4">
        @forelse($ticket->comments as $c)
          <div class="rounded-lg border border-gray-100 p-3">
            <div class="flex items-center justify-between">
              <div>
                <div class="text-sm font-medium text-gray-800">{{ $c->user->name ?? 'User' }}</div>
                <div class="text-xs text-gray-500">{{ $c->created_at->format('d M Y H:i') }}</div>
              </div>
              @auth
                @if(auth()->id() === $c->user_id || auth()->user()->role === 'IT')
                  <form method="POST" action="{{ route('comment.delete', $c->id) }}"
                        onsubmit="return confirm('Hapus komentar ini?')">
                    @csrf @method('DELETE')
                    <button class="text-xs text-red-600 hover:underline">Hapus</button>
                  </form>
                @endif
              @endauth
            </div>
            <div class="mt-2 text-gray-700 whitespace-pre-line">{{ $c->body }}</div>
          </div>
        @empty
          <div class="text-gray-500 text-sm">Belum ada komentar.</div>
        @endforelse
      </div>
    </div>
  </div>

  {{-- Kolom kanan: ringkasan --}}
  <aside class="space-y-4">
    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-5">
      <h4 class="font-medium text-gray-800 mb-3">Ringkasan</h4>
      <dl class="text-sm text-gray-700 space-y-2">
        <div class="flex justify-between"><dt>Nomor</dt><dd class="font-medium">{{ $ticket->nomor_tiket }}</dd></div>
        <div class="flex justify-between"><dt>Status</dt><dd class="font-medium">{{ $ticket->status }}</dd></div>
        <div class="flex justify-between"><dt>Kategori</dt><dd>{{ $ticket->kategori }}</dd></div>
        <div class="flex justify-between"><dt>Dibuat</dt><dd>{{ $ticket->created_at->format('d M Y H:i') }}</dd></div>
        <div class="flex justify-between"><dt>Handler</dt><dd>{{ $ticket->it->name ?? '-' }}</dd></div>
        <div class="flex justify-between"><dt>Eskalasi</dt><dd>{{ $ticket->eskalasi ?? '-' }}</dd></div>
      </dl>

      <div class="mt-4">
        <a href="{{ url()->previous() }}"
           class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-2 text-gray-700 hover:bg-gray-200">
          ← Kembali
        </a>
      </div>
    </div>
  </aside>
</div>

{{-- ===================== MODAL HISTORY (Alpine) ===================== --}}
<div x-data="{ open:false }"
     x-on:open-history.window="open=true"
     x-show="open" x-cloak
     class="fixed inset-0 z-[110] flex items-center justify-center">
  <div class="absolute inset-0 bg-black/40" @click="open=false"></div>

  <div class="relative bg-white w-full max-w-md mx-auto rounded-2xl shadow-xl p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-3">History Tiket</h3>

    <dl class="text-sm text-gray-700 space-y-2">
      <div class="flex justify-between">
        <dt>Tanggal dibuat</dt>
        <dd class="font-medium">{{ $ticket->created_at?->format('d M Y H:i') ?? '-' }}</dd>
      </div>

      <div class="flex justify-between">
        <dt>Tanggal progress</dt>
        <dd class="font-medium">
          {{ $ticket->taken_at?->format('d M Y H:i') ?? '-' }}
          @if($ticket->it) <span class="text-gray-500">— {{ $ticket->it->name }}</span> @endif
        </dd>
      </div>

      <div class="flex justify-between">
        <dt>Eskalasi</dt>
        <dd class="font-medium">{{ $ticket->eskalasi ?? '-' }}</dd>
      </div>

      <div class="flex justify-between">
        <dt>Tanggal closed</dt>
        <dd class="font-medium">{{ $ticket->closed_at?->format('d M Y H:i') ?? '-' }}</dd>
      </div>
    </dl>

    <div class="mt-5 flex justify-end">
      <button @click="open=false"
              class="inline-flex items-center px-3 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200">
        Tutup
      </button>
    </div>
  </div>
</div>
{{-- =================== /MODAL HISTORY (Alpine) ====================== --}}
@endsection
