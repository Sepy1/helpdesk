@extends('layouts.app')
@section('title', 'Detail Tiket')

@push('styles')
<style>
  /* Timeline (garis vertikal + titik + panah antar item) */
  .tl{position:relative;padding-left:1.5rem;background:linear-gradient(#e5e7eb,#e5e7eb) no-repeat;background-size:2px 100%;background-position:10px 0}
  .tl:before{content:"";position:absolute;left:10px;top:0;bottom:0;width:0;background:transparent}
  .tl-item{position:relative;padding-left:1rem;margin-left:.25rem}
  .tl-item:before{
    content:"";position:absolute;left:-6px;top:1.1rem;width:10px;height:10px;
    background:#fff;border:3px solid var(--dot,#4f46e5);border-radius:9999px
  }
  .tl-item:not(:last-child):after{
    content:"";position:absolute;left:6px;bottom:-12px;
    border-left:6px solid #e5e7eb;border-top:6px solid transparent;border-bottom:6px solid transparent
  }

  /* Timeline card enhancements (accent border + label styling) */
  .tl-card{border-left:4px solid var(--accent,#e5e7eb)}
  .tl-label{display:inline-flex;align-items:center;gap:.375rem;padding:.125rem .5rem;border-radius:9999px;background:#f3f4f6;color:#374151;font-size:11px;font-weight:600}
  @media (max-width: 640px){
    .tl{padding-left:1.25rem;background-position:8px 0}
    .tl-item{padding-left:.75rem}
    .tl-item:before{left:-9px}
  }

  /* Small helper for status badge */
  .status-badge {display:inline-flex;align-items:center;border-radius:9999px;padding:6px 10px;font-size:12px;font-weight:600;box-shadow:0 0 0 1px rgba(0,0,0,0.03) inset;}
</style>
@endpush

@section('content')
<div class="grid gap-6 lg:grid-cols-3">
  {{-- Kolom kiri (konten utama) --}}
  <div class="lg:col-span-2 h-full flex flex-col gap-6">
    {{-- Form tindak lanjut dipindahkan ke modal Update untuk tampilan lebih rapi --}}

    {{-- =========================
         CARD UTAMA: Nomor + Kategori + Handler + Deskripsi + Lampiran + Aksi
         ========================= --}}
    <div class="mt-6 bg-white rounded-2xl shadow-md ring-1 ring-gray-100 p-6">
      {{-- Header: nomor tiket + created info --}}
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4">
        <div class="min-w-0">
          <h2 class="text-xl font-semibold text-gray-800">#{{ $ticket->nomor_tiket }}</h2>
          <div class="text-sm text-gray-500 mt-1">
            Dibuat oleh: <span class="font-medium text-gray-700">{{ $ticket->user->name ?? '—' }}</span>
            @if(isset($ticket->cabang))
              · {{ $ticket->cabang }}
            @endif
            · {{ optional($ticket->created_at)->format('d M Y H:i') ?? '-' }}
          </div>
        </div>

        {{-- Status badge kanan atas --}}
        <div class="shrink-0">
          @php
            $statusColor = match($ticket->status) {
              'OPEN' => 'bg-green-50 text-green-700 ring-green-100',
              'TAKEN', 'ON_PROGRESS' => 'bg-amber-50 text-amber-700 ring-amber-100',
              'ESKALASI_VENDOR' => 'bg-fuchsia-50 text-fuchsia-700 ring-fuchsia-100',
              'VENDOR_RESOLVED' => 'bg-indigo-50 text-indigo-700 ring-indigo-100',
              'CLOSED' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
              default => 'bg-gray-50 text-gray-700 ring-gray-100',
            };
          @endphp
          <span class="status-badge {{ $statusColor }}">{{ $ticket->status }}</span>
        </div>
      </div>

      {{-- Dua kolom: Kategori | IT Handler --}}
      <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <div class="text-xs text-gray-500 mb-1">Kategori</div>
          <div class="text-lg font-medium text-gray-800">{{ $ticket->kategori }}</div>
        </div>
        <div>
          <div class="text-xs text-gray-500 mb-1">IT Handler</div>
          <div class="text-lg font-medium text-gray-800">{{ $ticket->it->name ?? '-' }}</div>
        </div>
      </div>

      {{-- Deskripsi --}}
      <div class="mt-6">
        <div class="text-xs text-gray-500 mb-1">Deskripsi</div>
        <div class="text-gray-800 whitespace-pre-line">{{ $ticket->deskripsi }}</div>
      </div>

      {{-- Lampiran + Aksi di bawah --}}
      <div class="mt-4 flex items-center justify-between">
        <div>
          <div class="text-xs text-gray-500 mb-1">Lampiran</div>
          @if($ticket->lampiran)
            <a href="{{ route('ticket.download',$ticket->id) }}" class="inline-flex items-center rounded-md px-3 py-2 text-sm ring-1 ring-gray-200 hover:bg-indigo-50 text-indigo-600" download data-noloader="1">
              Unduh lampiran
            </a>
          @else
            <div class="text-sm text-gray-400">-</div>
          @endif
        </div>

        {{-- area kanan untuk Eskalasi / Re-open / History --}}
        <div class="flex flex-wrap items-center gap-2 sm:gap-3 justify-end">
          @auth
            @if(auth()->user()->role === 'IT')
             
            @endif
          @endauth

          {{-- Re-open jika CLOSED --}}
          @auth
            @if(auth()->user()->role === 'IT' && $ticket->status === 'CLOSED')
              <form method="POST" action="{{ route('it.ticket.reopen', $ticket->id) }}">
                @csrf
                <button class="rounded-lg bg-amber-600 px-3 py-2 text-white text-sm hover:bg-amber-700">Re-open Tiket</button>
              </form>
            @endif
          @endauth

          <button type="button" x-data @click="$dispatch('open-history')" class="relative rounded-lg bg-blue-500 px-3 py-2 text-white text-sm hover:bg-blue-600">
            History
            <span id="history-badge" class="absolute -top-1 -right-1 inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-red-600 text-white text-[10px] font-bold hidden"></span>
          </button>
          @auth
            @if(auth()->user()->role === 'IT' || (auth()->user()->role === 'VENDOR' && $ticket->vendor_id === auth()->id()))
              <button type="button" x-data @click="$dispatch('open-update')" class="rounded-lg bg-emerald-600 px-3 py-2 text-white text-sm hover:bg-emerald-700">
                Update
              </button>
            @endif
          @endauth
        </div>
      </div>
    </div>
    {{-- ========================= END CARD UTAMA ========================= --}}

    {{-- Ringkasan (dipindah ke kiri agar komentar bisa lebih leluasa di kanan) --}}
    <div class="bg-white rounded-2xl shadow-lg ring-1 ring-gray-100 p-4 grow">
      <h4 class="font-medium text-gray-800 mb-3">Ringkasan</h4>
      <dl class="text-[13px] text-gray-700 space-y-1">
        <div class="flex justify-between"><dt>Nomor</dt><dd class="font-medium">{{ $ticket->nomor_tiket }}</dd></div>
        <div class="flex justify-between"><dt>Status</dt><dd class="font-medium">{{ $ticket->status }}</dd></div>
        <div class="flex justify-between"><dt>Kategori</dt><dd>{{ $ticket->kategori }}</dd></div>
        <div class="flex justify-between"><dt>Dibuat</dt><dd>{{ optional($ticket->created_at)->format('d M Y H:i') ?? '-' }}</dd></div>
        <div class="flex justify-between"><dt>Handler</dt><dd>{{ $ticket->it->name ?? '-' }}</dd></div>
        <div class="flex justify-between"><dt>Vendor</dt><dd>{{ $ticket->vendor->name ?? '-' }}</dd></div>
        <div class="flex justify-between"><dt>Eskalasi</dt><dd>{{ $ticket->escalated ?? 'TIDAK' }}</dd></div>
        <div class="flex justify-between"><dt>Taken At</dt><dd>{{ optional($ticket->taken_at)->format('d M Y H:i') ?? '-' }}</dd></div>
        <div class="flex justify-between"><dt>Closed At</dt><dd>{{ optional($ticket->closed_at)->format('d M Y H:i') ?? '-' }}</dd></div>
      </dl>

      
    </div>

  </div>

  {{-- Komentar kanan --}}
  <aside class="space-y-4 lg:mt-6 h-full">
    <div class="bg-white rounded-2xl shadow-md ring-1 ring-gray-100 p-4 h-full flex flex-col min-h-0">
      <div class="shrink-0 flex items-center justify-between">
        <div class="flex items-center">
          <h3 class="font-semibold text-gray-800">Komentar / Progres</h3>
          <span id="comment-badge" class="ml-2 inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-red-600 text-white text-[10px] font-bold align-middle hidden"></span>
        </div>
      
      </div>  

      <div id="chat-list" class="mt-3 flex-1 overflow-auto max-h-[50vh] sm:max-h-[56vh] pr-1 space-y-3">
        @forelse($ticket->comments->sortBy('created_at') as $c)
          @php $mine = auth()->check() && auth()->id() === $c->user_id; @endphp
          <div id="c-{{ $c->id }}" class="flex {{ $mine ? 'justify-end' : 'justify-start' }}" data-comment-ts="{{ optional($c->created_at)->format('c') }}">
            <div class="max-w-[85%] sm:max-w-[78%]">
              <div class="text-[11px] text-gray-500 leading-4 {{ $mine ? 'text-right' : '' }}">
                {{ $c->user->name ?? 'User' }} · {{ optional($c->created_at)->format('d M Y H:i') ?? '-' }}
              </div>
              <div class="mt-1 inline-block rounded-2xl px-3 py-2 text-sm leading-relaxed break-words shadow-sm {{ $mine ? 'bg-emerald-500 text-white rounded-br-sm' : 'bg-gray-100 text-gray-800 rounded-bl-sm' }}">
                {{ $c->body }}
                @if($c->attachment)
                  <div class="mt-2">
                    <a href="{{ route('comment.download', $c->id) }}" class="inline-flex items-center px-2 py-1 rounded-md ring-1 ring-white/40 text-xs {{ $mine ? 'bg-white/10 text-white hover:bg-white/20' : 'bg-white text-indigo-600 hover:bg-indigo-50 ring-indigo-200' }}" download data-noloader="1">Lampiran</a>
                  </div>
                @endif
              </div>
              <div class="mt-1 flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                @auth
                  @if($ticket->status !== 'CLOSED' && auth()->id() === $c->user_id)
                    <form method="POST" action="{{ route('comment.delete', $c->id) }}" onsubmit="return confirm('Hapus komentar ini?')">
                      @csrf @method('DELETE')
                      <button class="text-[11px] text-red-500/80 hover:text-red-600 hover:underline">Hapus</button>
                    </form>
                  @endif
                @endauth
              </div>
            </div>
          </div>
        @empty
          <div class="text-gray-500 text-sm">Belum ada komentar.</div>
        @endforelse
      </div>

      <div class="shrink-0 mt-3 border-t pt-3">
        @if($ticket->status !== 'CLOSED')
          <form action="{{ route('ticket.comment', $ticket->id) }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
            @csrf
            <textarea name="body" class="w-full flex-1 rounded-lg border-gray-300 resize-y min-h-[38px] max-h-[120px]" rows="2" required placeholder="Tulis pesan..."></textarea>
            <label id="attachBtn" class="shrink-0 inline-flex items-center justify-center w-10 h-10 rounded-full ring-1 ring-gray-200 bg-white hover:bg-gray-50 text-gray-600 cursor-pointer transition-colors self-end sm:self-auto" title="Lampirkan file">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79V7a5 5 0 00-9.9-1M3 13l7.5-7.5a3.5 3.5 0 015 5L9 19a4 4 0 11-5.657-5.657L14 2" />
              </svg>
              <input id="attachInput" type="file" name="attachment" class="sr-only" />
            </label>
            <button class="shrink-0 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg self-end sm:self-auto">Kirim</button>
          </form>
        @else
          <div class="rounded-lg bg-gray-50 text-gray-600 ring-1 ring-gray-200 px-4 py-3 text-sm">Tiket telah ditutup. Komentar dan lampiran dinonaktifkan.</div>
        @endif
      </div>
    </div>
  </aside>
</div>

{{-- ===================== MODAL HISTORY (Alpine) ===================== --}}
<div x-data="{ open:false }"
  x-on:open-history.window="open=true"
  x-show="open" x-cloak
  class="fixed inset-0 z-[110] flex items-start sm:items-center justify-center"
     role="dialog" aria-modal="true" aria-label="Riwayat tiket"
     @keydown.escape.window="open=false">

  {{-- Backdrop (fade) --}}
  <div class="absolute inset-0 bg-black/10 backdrop-blur-sm" x-transition.opacity @click="open=false" aria-hidden="true"></div>

  {{-- Panel modal --}}
  <div id="history-panel" class="relative w-full max-w-md sm:max-w-lg md:max-w-xl lg:max-w-3xl xl:max-w-4xl mx-auto mx-4 mt-4 sm:mt-0 rounded-2xl bg-white shadow-xl p-6"
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
       x-transition:enter-end="opacity-100 scale-100 translate-y-0"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100 scale-100 translate-y-0"
       x-transition:leave-end="opacity-0 scale-95 -translate-y-1">

    <div class="flex items-start justify-between mb-4">
      <h3 class="text-lg font-semibold text-gray-800">History Tiket</h3>
      <div class="flex items-center gap-2">
        <button type="button" class="px-3 h-8 inline-flex items-center rounded-lg text-sm text-indigo-600 ring-1 ring-indigo-200 hover:bg-indigo-50" onclick="window.downloadHistoryPanel && window.downloadHistoryPanel()">Download PNG</button>
        <button class="h-8 w-8 inline-flex items-center justify-center rounded-md hover:bg-gray-100" @click="open=false" aria-label="Tutup">✕</button>
      </div>
    </div>

    <ul id="history-list" class="tl space-y-5 max-h-80 overflow-auto pr-1">
      @php
        $labels = [
          'created' => 'Dibuat',
          'taken' => 'Diambil IT',
          'released' => 'Dilepas ke Antrian',
          'progress' => 'Progres IT',
          'assigned_vendor' => 'Assign ke Vendor',
          'assign_vendor_cleared' => 'Hapus Assign Vendor',
          'vendor_followup' => 'Tindak Lanjut Vendor',
          'closed' => 'Ditutup',
          'reopened' => 'Dibuka Kembali',
        ];
        $colors = [
          'created' => '#4f46e5',
          'taken' => '#f59e0b',
          'released' => '#6b7280',
          'progress' => '#f59e0b',
          'assigned_vendor' => '#a21caf',
          'assign_vendor_cleared' => '#6b7280',
          'vendor_followup' => '#a21caf',
          'closed' => '#059669',
          'reopened' => '#0ea5e9',
        ];
      @endphp

      @forelse($ticket->histories->sortBy('created_at') as $h)
        @php
          $dot = $colors[$h->action] ?? '#4b5563';
          $label = $labels[$h->action] ?? ucfirst(str_replace('_',' ', $h->action));
          $meta = $h->meta ?? [];
        @endphp
        <li id="h-{{ $h->id }}" class="tl-item" style="--dot: {{ $dot }}; --accent: {{ $dot }}" data-history-ts="{{ optional($h->created_at)->format('c') }}">
          <div class="tl-card rounded-xl border border-gray-200 bg-white p-4 shadow-md">
            <div class="flex items-center justify-between gap-3">
              <span class="tl-label">{{ $label }}</span>
              <div class="shrink-0 text-xs text-gray-500">{{ optional($h->created_at)->format('d M Y H:i') ?? '-' }}</div>
            </div>
            <div class="mt-1 text-xs text-gray-500">Oleh: {{ optional($h->user)->name ?? '-' }}</div>
            @if($h->action === 'assigned_vendor' && (!empty($meta['vendor_name']) || !empty($meta['vendor_id'])))
              <div class="mt-2 inline-flex items-center rounded-full bg-fuchsia-50 px-2 py-0.5 text-[11px] font-medium text-fuchsia-700 ring-1 ring-fuchsia-100">
                {{ $meta['vendor_name'] ?? ('Vendor ID '.$meta['vendor_id']) }}
              </div>
            @endif
            @if($h->note)
              <div class="mt-2 text-sm text-gray-800 whitespace-pre-line">{{ $h->note }}</div>
            @endif
          </div>
        </li>
      @empty
        <li class="tl-item" style="--dot:#6b7280; --accent:#6b7280">
          <div class="tl-card rounded-xl border border-gray-200 bg-white p-4 shadow-md">
            <div class="text-sm text-gray-600">Belum ada log.</div>
          </div>
        </li>
      @endforelse
    </ul>
  </div>
</div>
{{-- =================== /MODAL HISTORY ====================== --}}

{{-- ===================== MODAL UPDATE (Alpine) ===================== --}}
<div x-data="{ open:false }"
  x-on:open-update.window="open=true"
  x-show="open" x-cloak
  class="fixed inset-0 z-[120] flex items-start sm:items-center justify-center"
     role="dialog" aria-modal="true" aria-label="Update tiket"
     @keydown.escape.window="open=false">

  <div class="absolute inset-0 bg-black/10 backdrop-blur-sm" x-transition.opacity @click="open=false" aria-hidden="true"></div>

  <div class="relative w-full max-w-2xl mx-auto mx-4 mt-4 sm:mt-0 rounded-2xl bg-white shadow-xl p-6"
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
       x-transition:enter-end="opacity-100 scale-100 translate-y-0"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100 scale-100 translate-y-0"
       x-transition:leave-end="opacity-0 scale-95 -translate-y-1">

    <div class="flex items-start justify-between mb-4">
      <h3 class="text-lg font-semibold text-gray-800">Update Tiket</h3>
      <button class="h-8 w-8 inline-flex items-center justify-center rounded-md hover:bg-gray-100" @click="open=false" aria-label="Tutup">✕</button>
    </div>

    @auth
      @if(auth()->user()->role === 'IT')
        <div class="space-y-6">
          <div>
            <h4 class="text-sm font-semibold text-gray-800 mb-2">Assign ke Vendor</h4>
            <form method="POST" action="{{ route('it.ticket.assign_vendor', $ticket->id) }}" class="flex flex-wrap items-center gap-2">
              @csrf
              <select name="vendor_id" class="rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                <option value="" {{ empty($ticket->vendor_id) ? 'selected' : '' }}>Tidak</option>
                @foreach(($vendors ?? collect()) as $v)
                  <option value="{{ $v->id }}" @selected($ticket->vendor_id === $v->id)>{{ $v->name }}</option>
                @endforeach
              </select>
              <div class="ml-auto flex gap-2">
                <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-2 text-white text-sm hover:bg-emerald-700">Assign</button>
              </div>
            </form>
          </div>

          @if(!empty($ticket->vendor_followup))
            <div class="rounded-xl border border-gray-200 bg-white p-4">
              <div class="text-xs font-semibold text-gray-700">Tindak Lanjut Vendor</div>
              <div class="mt-1 text-sm text-gray-800 whitespace-pre-line">{{ $ticket->vendor_followup }}</div>
              @if($ticket->vendor_followup_at)
                <div class="mt-1 text-xs text-gray-500">Diperbarui: {{ optional($ticket->vendor_followup_at)->format('d M Y H:i') ?? '-' }}</div>
              @endif
            </div>
          @endif

          <form method="POST" action="{{ route('it.ticket.close', $ticket->id) }}" class="space-y-4">
            @csrf
            <div>
              <label class="text-sm font-medium text-gray-700">Root Cause</label>
              <select name="root_cause" required class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                @php $rcOld = old('root_cause', $ticket->root_cause); @endphp
                <option value="" disabled {{ empty($rcOld) ? 'selected' : '' }}>Pilih Root Cause</option>
                <option value="Human Error" @selected($rcOld === 'Human Error')>Human Error</option>
                <option value="Pergantian User" @selected($rcOld === 'Pergantian User')>Pergantian User</option>
                <option value="Penyesuaian Sistem" @selected($rcOld === 'Penyesuaian Sistem')>Penyesuaian Sistem</option>
                <option value="Bug Sistem" @selected($rcOld === 'Bug Sistem')>Bug Sistem</option>
                <option value="Kerusakan Hardware" @selected($rcOld === 'Kerusakan Hardware')>Kerusakan Hardware</option>
                <option value="Kerusakan Software" @selected($rcOld === 'Kerusakan Software')>Kerusakan Software</option>
                <option value="ISP Down" @selected($rcOld === 'ISP Down')>ISP Down</option>
                <option value="Wireless Down" @selected($rcOld === 'Wireless Down')>Wireless Down</option>
                <option value="Lainnya" @selected($rcOld === 'Lainnya')>Lainnya</option>
              </select>
            </div>

            <div>
              <label class="text-sm font-medium text-gray-700">Tindak Lanjut</label>
              <textarea name="closed_note" rows="3" required class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Tuliskan tindak lanjut penyelesaian...">{{ old('closed_note') }}</textarea>
            </div>
            <div class="flex justify-end gap-2">
              <button type="button" class="rounded-lg px-3 py-2 text-sm text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50" @click="open=false">Batal</button>
              <button type="submit" class="rounded-lg bg-red-600 px-3 py-2 text-white text-sm hover:bg-red-700">Close Ticket</button>
            </div>
          </form>
        </div>
      @elseif(auth()->user()->role === 'VENDOR' && $ticket->vendor_id === auth()->id())
        <form method="POST" action="{{ route('vendor.ticket.followup', $ticket->id) }}" class="space-y-4">
          @csrf
          <div>
            <label class="text-sm font-medium text-gray-700">Tindak Lanjut Vendor</label>
            <textarea name="vendor_followup" rows="3" required class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Catat tindak lanjut...">{{ old('vendor_followup', $ticket->vendor_followup) }}</textarea>
          </div>
          <div class="flex justify-end gap-2">
            <button type="button" class="rounded-lg px-3 py-2 text-sm text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50" @click="open=false">Batal</button>
            <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-2 text-white text-sm hover:bg-emerald-700">Simpan</button>
          </div>
        </form>
      @endif
    @endauth

  </div>
</div>
{{-- =================== /MODAL UPDATE ====================== --}}
  @push('scripts')
  <script>
    window.downloadHistoryPanel = async function(){
      try{
        const panel = document.getElementById('history-panel');
        const list = document.getElementById('history-list');
        if(!panel || !list){ return; }
        if(!window.html2canvas){
          await new Promise((resolve, reject)=>{
            const s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js';
            s.onload = resolve; s.onerror = reject; document.head.appendChild(s);
          });
        }
        const classesToRemove = ['max-h-80','overflow-auto','pr-1'];
        const removed = [];
        classesToRemove.forEach(c=>{ if(list.classList.contains(c)){ list.classList.remove(c); removed.push(c);} });
        const prevStyle = { maxHeight: list.style.maxHeight, overflow: list.style.overflow };
        list.style.maxHeight = 'none';
        list.style.overflow = 'visible';
        await new Promise(r=>setTimeout(r,0));
        const canvas = await html2canvas(panel, { backgroundColor: '#ffffff', scale: 2, useCORS: true });
        const link = document.createElement('a');
        link.download = 'history_{{ Str::slug($ticket->nomor_tiket ?? "tiket") }}.png';
        link.href = canvas.toDataURL('image/png');
        document.body.appendChild(link);
        link.click();
        link.remove();
        // restore
        list.style.maxHeight = prevStyle.maxHeight;
        list.style.overflow = prevStyle.overflow;
        removed.forEach(c=>list.classList.add(c));
      }catch(e){ console.error('Download PNG failed', e); }
    }
    // Auto-scroll chat to bottom on load
    document.addEventListener('DOMContentLoaded', function(){
      const list = document.getElementById('chat-list');
      if(list){ list.scrollTop = list.scrollHeight; }
      // Toggle attachment button color when a file is chosen
      const attachInput = document.getElementById('attachInput');
      const attachBtn = document.getElementById('attachBtn');
      if(attachInput && attachBtn){
        const setActive = (on)=>{
          attachBtn.classList.toggle('ring-emerald-300', on);
          attachBtn.classList.toggle('bg-emerald-50', on);
          attachBtn.classList.toggle('text-emerald-700', on);
          attachBtn.classList.toggle('hover:bg-emerald-100', on);
          attachBtn.classList.toggle('ring-gray-200', !on);
          attachBtn.classList.toggle('bg-white', !on);
          attachBtn.classList.toggle('text-gray-600', !on);
          attachBtn.classList.toggle('hover:bg-gray-50', !on);
        };
        attachInput.addEventListener('change', ()=> setActive(attachInput.files && attachInput.files.length>0));
      }

      // Deep-link handling: open History modal and scroll to item when URL hash points to it
      try{
        const hash = location.hash || '';
        if(hash.startsWith('#h-')){
          // open history modal (Alpine custom event)
          window.dispatchEvent(new CustomEvent('open-history'));
          // wait a tick for modal render
          setTimeout(()=>{
            const el = document.querySelector(hash);
            if(el){ el.scrollIntoView({ behavior:'smooth', block:'start' }); }
          }, 250);
        }else if(hash.startsWith('#c-')){
          const el = document.querySelector(hash);
          if(el){ el.scrollIntoView({ behavior:'smooth', block:'start' }); }
        }
      }catch(_){ }

      // ===== Notifications (badges) for History and Comments =====
      const ticketId = {{ (int) $ticket->id }};
      const cmKey = `ticket:${ticketId}:seen:comments`;
      const hsKey = `ticket:${ticketId}:seen:history`;
      const commentBadge = document.getElementById('comment-badge');
      const historyBadge = document.getElementById('history-badge');

      const parseTs = (s)=>{
        const t = Date.parse(s);
        return isNaN(t) ? 0 : t;
      };

      const getCommentTimestamps = ()=> Array.from(document.querySelectorAll('[data-comment-ts]'))
        .map(el => parseTs(el.getAttribute('data-comment-ts')))
        .filter(Boolean)
        .sort((a,b)=>a-b);

      const getHistoryTimestamps = ()=> Array.from(document.querySelectorAll('[data-history-ts]'))
        .map(el => parseTs(el.getAttribute('data-history-ts')))
        .filter(Boolean)
        .sort((a,b)=>a-b);

      const showBadge = (el, count)=>{
        if(!el) return;
        if(count > 0){
          el.textContent = count > 99 ? '99+' : String(count);
          el.classList.remove('hidden');
        } else {
          el.classList.add('hidden');
        }
      };

      // Initialize last-seen to the latest existing item to avoid showing legacy as unread
      const initSeenIfMissing = ()=>{
        if(localStorage.getItem(cmKey) === null){
          const ts = getCommentTimestamps();
          if(ts.length){ localStorage.setItem(cmKey, String(ts[ts.length-1])); }
          else { localStorage.setItem(cmKey, String(Date.now())); }
        }
        if(localStorage.getItem(hsKey) === null){
          const ts = getHistoryTimestamps();
          if(ts.length){ localStorage.setItem(hsKey, String(ts[ts.length-1])); }
          else { localStorage.setItem(hsKey, String(Date.now())); }
        }
      };

      const updateBadges = ()=>{
        const cmSeen = parseInt(localStorage.getItem(cmKey) || '0', 10);
        const hsSeen = parseInt(localStorage.getItem(hsKey) || '0', 10);
        const cmTs = getCommentTimestamps();
        const hsTs = getHistoryTimestamps();
        const cmCount = cmTs.filter(t => t > cmSeen).length;
        const hsCount = hsTs.filter(t => t > hsSeen).length;
        showBadge(commentBadge, cmCount);
        showBadge(historyBadge, hsCount);
      };

      initSeenIfMissing();
      updateBadges();

      const markCommentsSeen = ()=>{
        localStorage.setItem(cmKey, String(Date.now()));
        updateBadges();
      };
      const markHistorySeen = ()=>{
        localStorage.setItem(hsKey, String(Date.now()));
        updateBadges();
      };

      // Mark comments seen when focusing composer or reaching bottom
      const composer = document.querySelector('textarea[name="body"]');
      if(composer){
        composer.addEventListener('focus', markCommentsSeen);
      }
      if(list){
        list.addEventListener('scroll', ()=>{
          const threshold = 12; // px from bottom
          if(list.scrollHeight - list.scrollTop - list.clientHeight < threshold){
            markCommentsSeen();
          }
        });
      }
      const form = document.querySelector('form[action*="ticket.comment"]');
      if(form){
        form.addEventListener('submit', ()=>{
          // Assume user has seen latest after sending
          markCommentsSeen();
        });
      }

      // Mark history seen when opening the history modal (listen to Alpine custom event)
      window.addEventListener('open-history', markHistorySeen);
    });
  </script>
  @endpush
@endsection
