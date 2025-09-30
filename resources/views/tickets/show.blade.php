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
      <div class="mt-6 flex flex-col gap-3">
        @auth
          @if(auth()->user()->role === 'IT')
            {{-- Eskalasi --}}
            <form method="POST" action="{{ route('it.ticket.eskalasi', $ticket->id) }}" class="flex flex-wrap items-center gap-2">
              @csrf
              <label class="text-sm text-gray-600">Eskalasi</label>
              <select name="eskalasi" class="rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                <option value="TIDAK"  @selected($ticket->eskalasi==='TIDAK')>Tidak</option>
                <option value="VENDOR" @selected($ticket->eskalasi==='VENDOR')>Vendor</option>
              </select>
              <button class="rounded-lg bg-gray-900 px-3 py-2 text-white hover:bg-gray-800 text-sm">Simpan</button>
            </form>

            {{-- Tindak lanjut vendor (muncul saat eskalasi vendor) --}}
            @if($ticket->eskalasi === 'VENDOR')
            <form method="POST" action="{{ route('it.ticket.vendor_followup', $ticket->id) }}" class="flex flex-col sm:flex-row gap-2 sm:items-center">
              @csrf
              <textarea name="vendor_followup" rows="2" required
                        class="flex-1 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Catat tindak lanjut dari vendor...">{{ old('vendor_followup', $ticket->vendor_followup) }}</textarea>
              <button class="rounded-lg bg-indigo-600 px-3 py-2 text-white hover:bg-indigo-700 text-sm shrink-0">Simpan</button>
            </form>
            @endif

            {{-- Tutup tiket + catatan tindak lanjut internal --}}
            @if($ticket->status !== 'CLOSED')
            <form method="POST" action="{{ route('it.ticket.close', $ticket->id) }}" class="flex flex-col sm:flex-row gap-2 sm:items-center">
              @csrf
              <textarea name="closed_note" rows="2" required
                        class="flex-1 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Ringkasan tindak lanjut penyelesaian...">{{ old('closed_note') }}</textarea>
              <button class="rounded-lg bg-emerald-600 px-3 py-2 text-white hover:bg-emerald-700 text-sm shrink-0">Tutup Tiket</button>
            </form>
            @endif
          @endif
        @endauth

        {{-- Tombol History (semua role bisa) --}}
        <div>
          <button type="button"
                  x-data
                  @click="$dispatch('open-history')"
                  class="rounded-lg bg-white ring-1 ring-gray-200 px-3 py-2 text-gray-700 hover:bg-gray-50">
            History
          </button>
        </div>
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
        <div class="flex justify-between"><dt>Taken At</dt><dd>{{ optional($ticket->taken_at)->format('d M Y H:i') ?? '-' }}</dd></div>
        <div class="flex justify-between"><dt>Closed At</dt><dd>{{ optional($ticket->closed_at)->format('d M Y H:i') ?? '-' }}</dd></div>
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
     class="fixed inset-0 z-[110] flex items-start justify-center"
     role="dialog" aria-modal="true" aria-label="Riwayat tiket"
     @keydown.escape.window="open=false">

  {{-- Backdrop (fade) --}}
  <div class="absolute inset-0 bg-black/40"
       x-transition.opacity
       @click="open=false"
       aria-hidden="true"></div>

  {{-- Panel modal --}}
  <div class="relative w-full max-w-md mx-auto mt-4 rounded-2xl bg-white shadow-xl p-6"
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
       x-transition:enter-end="opacity-100 scale-100 translate-y-0"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100 scale-100 translate-y-0"
       x-transition:leave-end="opacity-0 scale-95 -translate-y-1">

    {{-- Header --}}
    <div class="flex items-start justify-between mb-4">
      <h3 class="text-lg font-semibold text-gray-800">History Tiket</h3>
      <button class="h-8 w-8 inline-flex items-center justify-center rounded-md hover:bg-gray-100"
              @click="open=false" aria-label="Tutup">
        ✕
      </button>
    </div>

    {{-- ====== TIMELINE dengan panah ====== --}}
<style>
  /* Garis & panah */
  .tl{position:relative;padding-left:1.5rem}
  .tl:before{content:"";position:absolute;left:10px;top:0;bottom:0;width:2px;background:#e5e7eb} /* garis vertikal */
  .tl-item{position:relative;padding-left:1rem;margin-left:.25rem}
  /* titik (warna mengikuti --dot) */
  .tl-item:before{
    content:"";position:absolute;left:-6px;top:1.1rem;width:10px;height:10px;
    background:#fff;border:3px solid var(--dot,#4f46e5);border-radius:9999px
  }
  /* panah ke bawah di garis (tidak pada item terakhir) */
  .tl-item:not(:last-child):after{
    content:"";position:absolute;left:6px;bottom:-12px;
    border-left:6px solid #e5e7eb;border-top:6px solid transparent;border-bottom:6px solid transparent
  }
</style>

<ul class="tl space-y-5">

 {{-- Created --}}
<li class="tl-item" style="--dot:#4f46e5">
  <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
    <div class="text-xs font-medium uppercase tracking-wide text-indigo-600">Created</div>

    <div class="mt-1 text-sm text-gray-800">
      {{ $ticket->created_at->format('d M Y H:i') }}
    </div>
    

    {{-- Kategori --}}
    <div class="mt-2">
      @php
        $badge = match($ticket->kategori){
          'JARINGAN' => 'bg-blue-100 text-blue-800 ring-blue-200',
          'LAYANAN'  => 'bg-violet-100 text-violet-800 ring-violet-200',
          'CBS'      => 'bg-rose-100 text-rose-800 ring-rose-200',
          default    => 'bg-gray-100 text-gray-800 ring-gray-200',
        };
      @endphp
      <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 {{ $badge }}">
        {{ $ticket->kategori }}
      </span>
      
    </div>

    {{-- Deskripsi singkat --}}
    <div class="mt-2 text-sm text-gray-700">
      @php
        use Illuminate\Support\Str;
        $short = Str::limit(strip_tags($ticket->deskripsi), 140);
      @endphp
      {{ $short }}
    </div>
  </div>
</li>

  {{-- Proses --}}
  @if($ticket->taken_at)
  <li class="tl-item" style="--dot:#f59e0b">
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
      <div class="text-xs font-medium uppercase tracking-wide text-amber-600">Proses</div>
      <div class="mt-1 text-sm text-gray-800">
        {{ $ticket->taken_at->format('d M Y H:i') }}
      </div>
      <div class="mt-1 text-xs text-gray-500">
        IT Handler: <span class="font-medium text-gray-700">{{ $ticket->it->name ?? '-' }}</span>
      <div class="mt-2">
      <div class="text-xs text-gray-500">Tindakan</div>
      <div class="mt-0.5 text-sm text-gray-800 whitespace-pre-line">
        {{ $ticket->closed_note ?? '—' }}
      </div>
    </div>
      </div>
    </div>


  </li>
  @endif

  {{-- Eskalasi --}}
  @if(!empty($ticket->eskalasi))
  <li class="tl-item" style="--dot:#a21caf">
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
      <div class="flex items-center gap-2">
        <div class="text-xs font-medium uppercase tracking-wide text-fuchsia-600">Eskalasi</div>
        <span class="inline-flex items-center rounded-full bg-fuchsia-50 px-2 py-0.5 text-[11px] font-medium text-fuchsia-700 ring-1 ring-fuchsia-100">
          {{ $ticket->eskalasi }}
        </span>
      </div>
      @if($ticket->eskalasi === 'VENDOR')
        <div class="mt-2">
          <div class="text-xs text-gray-500">Tindak lanjut vendor</div>
          <div class="mt-0.5 text-sm text-gray-800 whitespace-pre-line">{{ $ticket->vendor_followup ?? '—' }}</div>
          @if($ticket->vendor_followup_at)
            <div class="mt-1 text-xs text-gray-500">
              Diperbarui: {{ $ticket->vendor_followup_at->format('d M Y H:i') }}
            </div>
          @endif
        </div>
      @endif
    </div>
  </li>
  @endif

 {{-- Closed --}}
@if($ticket->closed_at)
<li class="tl-item" style="--dot:#059669">
  <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
    <div class="text-xs font-medium uppercase tracking-wide text-emerald-600">Closed</div>

    <div class="mt-1 text-sm text-gray-800">
      {{ $ticket->closed_at->format('d M Y H:i') }}
    </div>

    <div class="mt-1 text-xs text-gray-500">
      IT Handler (penutup): 
      <span class="font-medium text-gray-700">{{ $ticket->it->name ?? '-' }}</span>
    </div>

    
  </div>
</li>
@endif
</ul>
{{-- ====== /TIMELINE dengan panah ====== --}}

@endsection
