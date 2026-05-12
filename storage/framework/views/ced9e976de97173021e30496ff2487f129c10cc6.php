<?php $__env->startSection('title', 'Detail Tiket'); ?>

<?php $__env->startPush('styles'); ?>
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
  .status-badge {display:inline-flex;align-items:center;border-radius:9999px;padding:5px 8px;font-size:11px;font-weight:600;box-shadow:0 0 0 1px rgba(0,0,0,0.03) inset;}

  /* Flat card style with stronger border */
  .show-card{
    border: 1px solid #cbd5e1;
    box-shadow: none;
    transition: border-color .18s ease, background-color .18s ease, transform .18s ease;
  }
  .show-card:hover{
    border-color: #94a3b8;
    background-color: #f9fafb;
    transform: translateY(-1px);
  }

</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<div class="flex flex-col lg:min-h-[calc(100dvh-6.25rem)] lg:max-h-[calc(100dvh-6.25rem)]">
<div class="grid flex-1 grid-cols-1 gap-6 lg:min-h-0 lg:overflow-hidden lg:grid-cols-3 lg:items-stretch">
  
  <div class="flex flex-col gap-6 overflow-visible lg:col-span-2 lg:h-full lg:min-h-0 lg:overflow-y-auto lg:overflow-x-hidden">
    

    
    <div class="show-card mt-0 shrink-0 bg-white rounded-2xl p-3 text-xs sm:p-5 sm:text-sm">
      
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4">
          <div class="min-w-0">
            <h2 class="text-lg font-semibold text-gray-800 flex flex-wrap items-baseline gap-x-2 gap-y-0.5">
              <span>#<?php echo e($ticket->nomor_tiket); ?></span>
              <?php
                $namaCabangPembuat = $ticket->user?->kodeKantor?->nama_kantor;
                $kodeCabangPembuat = $ticket->user?->kode_kantor;
              ?>
              <?php if($namaCabangPembuat || $kodeCabangPembuat): ?>
                <span class="text-sm font-normal text-gray-500">· <?php echo e($namaCabangPembuat ?? $kodeCabangPembuat); ?></span>
              <?php endif; ?>
            </h2>
            <div class="text-xs text-gray-500 mt-1">
            Dibuat oleh: <span class="font-medium text-gray-700"><?php echo e($ticket->user->name ?? '—'); ?></span>
            <?php if(isset($ticket->cabang)): ?>
              · <?php echo e($ticket->cabang); ?>

            <?php endif; ?>
            · <?php echo e(optional($ticket->created_at)->format('d M Y H:i') ?? '-'); ?>

          </div>
        </div>

        
        <div class="shrink-0">
          <?php
            $statusColor = match($ticket->status) {
              'OPEN' => 'bg-green-50 text-green-700 ring-green-100',
              'TAKEN', 'ON_PROGRESS' => 'bg-amber-50 text-amber-700 ring-amber-100',
              'ESKALASI_VENDOR' => 'bg-fuchsia-50 text-fuchsia-700 ring-fuchsia-100',
              'VENDOR_RESOLVED' => 'bg-indigo-50 text-indigo-700 ring-indigo-100',
              'CLOSED' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
              default => 'bg-gray-50 text-gray-700 ring-gray-100',
            };
          ?>
          <span class="status-badge <?php echo e($statusColor); ?>"><?php echo e($ticket->status); ?></span>
        </div>
      </div>

      
      <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <div class="text-xs text-gray-500 mb-1">Kategori</div>
          <div class="text-sm font-medium text-gray-800"><?php echo e($ticket->kategori); ?></div>
        </div>
        <div>
          <div class="text-xs text-gray-500 mb-1">IT Handler</div>
          <div class="text-sm font-medium text-gray-800"><?php echo e($ticket->it->name ?? '-'); ?></div>
        </div>
      </div>

      
      <div class="mt-6">
        <div class="text-xs text-gray-500 mb-1">Deskripsi</div>
        <div class="text-sm text-gray-800 whitespace-pre-line"><?php echo e($ticket->deskripsi); ?></div>
      </div>

      
      <div class="mt-4 flex items-center justify-between">
        <div>
          <div class="text-xs text-gray-500 mb-1">Lampiran</div>
          
          <?php if($ticket->lampiran): ?>
            <div>
              <a href="<?php echo e(route('ticket.download',$ticket->id)); ?>?inline=1" target="_blank" rel="noopener" class="inline-flex max-w-full items-center rounded-md px-2 py-1 text-left text-xs text-indigo-600 ring-1 ring-gray-200 hover:bg-indigo-50 break-all whitespace-normal">Lihat: <?php echo e(basename($ticket->lampiran)); ?></a>
              <a href="<?php echo e(route('ticket.download',$ticket->id)); ?>" class="mt-1 block text-xs text-gray-600 hover:underline sm:ml-2 sm:mt-0 sm:inline">Unduh</a>
            </div>
          <?php else: ?>
            <div class="text-xs text-gray-400">-</div>
          <?php endif; ?>
        </div>

        
        <div class="flex flex-wrap items-center gap-2 sm:gap-3 justify-end">
          <?php if(auth()->guard()->check()): ?>
            <?php if(auth()->user()->role === 'IT'): ?>
             
            <?php endif; ?>
          <?php endif; ?>

          
          <?php if(auth()->guard()->check()): ?>
            <?php if(auth()->user()->role === 'IT' && $ticket->status === 'CLOSED'): ?>
              <form method="POST" action="<?php echo e(route('it.ticket.reopen', $ticket->id)); ?>">
                <?php echo csrf_field(); ?>
                <button class="rounded-lg bg-amber-600 px-3 py-2 text-white text-sm hover:bg-amber-700">Re-open Tiket</button>
              </form>
            <?php endif; ?>
          <?php endif; ?>

          <button type="button" x-data @click="$dispatch('open-history')" class="relative rounded-md bg-blue-500 px-2 py-1 text-white text-xs hover:bg-blue-600">
            History
            <span id="history-badge" class="absolute -top-1 -right-1 inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-red-600 text-white text-[10px] font-bold hidden"></span>
          </button>
          <?php if(auth()->guard()->check()): ?>
            <?php if(auth()->user()->role === 'IT' || (auth()->user()->role === 'VENDOR' && $ticket->vendor_id === auth()->id())): ?>
              <button type="button" x-data @click="$dispatch('open-update')" class="rounded-md bg-emerald-600 px-2 py-1 text-white text-xs hover:bg-emerald-700">
                Update
              </button>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
    

    
    <div class="show-card min-h-0 flex-1 rounded-2xl bg-white p-3 text-xs sm:p-5 sm:text-sm lg:min-h-0 lg:overflow-y-auto">
      <h4 class="font-medium text-gray-800 mb-3">Ringkasan</h4>
      <dl class="text-[13px] text-gray-700 space-y-1">
        <div class="flex justify-between gap-2"><dt class="shrink-0">Nomor</dt><dd class="font-medium text-right">
          <?php echo e($ticket->nomor_tiket); ?>

          <?php if($ticket->user?->kodeKantor?->nama_kantor || $ticket->user?->kode_kantor): ?>
            <span class="block text-xs font-normal text-gray-500"><?php echo e($ticket->user?->kodeKantor?->nama_kantor ?? $ticket->user?->kode_kantor); ?></span>
          <?php endif; ?>
        </dd></div>
        <div class="flex justify-between"><dt>Status</dt><dd class="font-medium"><?php echo e($ticket->status); ?></dd></div>
        <div class="flex justify-between"><dt>Kategori</dt><dd><?php echo e($ticket->kategori); ?></dd></div>
        <div class="flex justify-between"><dt>Dibuat</dt><dd><?php echo e(optional($ticket->created_at)->format('d M Y H:i') ?? '-'); ?></dd></div>
        <div class="flex justify-between"><dt>Handler</dt><dd><?php echo e($ticket->it->name ?? '-'); ?></dd></div>
        <div class="flex justify-between"><dt>Vendor</dt><dd><?php echo e($ticket->vendor->name ?? '-'); ?></dd></div>
        <div class="flex justify-between"><dt>Eskalasi</dt><dd><?php echo e($ticket->escalated ?? 'TIDAK'); ?></dd></div>
        <div class="flex justify-between"><dt>Taken At</dt><dd><?php echo e(optional($ticket->taken_at)->format('d M Y H:i') ?? '-'); ?></dd></div>
        <div class="flex justify-between"><dt>Closed At</dt><dd><?php echo e(optional($ticket->closed_at)->format('d M Y H:i') ?? '-'); ?></dd></div>
        <?php if($ticket->status === 'CLOSED' && $ticket->rootCauseDetail): ?>
          <div class="flex justify-between gap-2"><dt class="shrink-0">Detail root cause</dt><dd class="text-right font-medium text-gray-800"><?php echo e($ticket->rootCauseDetail->label); ?></dd></div>
        <?php endif; ?>
      </dl>

      
    </div>

  </div>

  
  <aside class="flex min-h-[14rem] flex-col lg:min-h-0 lg:h-full">
    <div class="show-card flex flex-1 flex-col overflow-hidden rounded-2xl bg-white p-3 text-xs sm:p-5 sm:text-sm lg:min-h-0 lg:h-full lg:max-h-full">
      <div class="shrink-0 flex items-center justify-between">
        <div class="flex items-center">
          <h3 class="font-semibold text-gray-800">Komentar / Progres</h3>
          <span id="comment-badge" class="ml-2 inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-red-600 text-white text-[10px] font-bold align-middle hidden"></span>
        </div>
      
      </div>  

      <div id="chat-list" class="mt-3 min-h-0 flex-1 space-y-2 overflow-visible pr-1 min-h-[10rem] lg:min-h-[12rem] lg:overflow-y-auto lg:overflow-x-hidden lg:overscroll-contain">
        <?php $__empty_1 = true; $__currentLoopData = $ticket->comments->sortBy('created_at'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <?php $mine = auth()->check() && auth()->id() === $c->user_id; ?>
          <div id="c-<?php echo e($c->id); ?>" class="flex <?php echo e($mine ? 'justify-end' : 'justify-start'); ?>" data-comment-ts="<?php echo e(optional($c->created_at)->format('c')); ?>">
            <div class="max-w-[85%] sm:max-w-[78%]">
              <?php
                $seenByReporter = isset($ticket->seen_by_reporter_at) && $ticket->seen_by_reporter_at && $c->created_at && $ticket->seen_by_reporter_at->gte($c->created_at);
                $seenByIt = isset($ticket->seen_by_it_at) && $ticket->seen_by_it_at && $c->created_at && $ticket->seen_by_it_at->gte($c->created_at);
              ?>
              <div class="text-[10px] text-gray-500 leading-4 <?php echo e($mine ? 'text-right' : ''); ?>">
                <?php echo e($c->user->name ?? 'User'); ?> · <?php echo e(optional($c->created_at)->format('d M Y H:i') ?? '-'); ?>

                <?php if($seenByReporter): ?>
                  <svg xmlns="http://www.w3.org/2000/svg" class="inline-block h-4 w-4 text-emerald-600 ml-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" title="Dibaca oleh pelapor">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                  </svg>
                <?php endif; ?>
                <?php if($seenByIt): ?>
                  <svg xmlns="http://www.w3.org/2000/svg" class="inline-block h-4 w-4 text-sky-600 ml-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" title="Dibaca oleh IT">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                  </svg>
                <?php endif; ?>
              </div>
              <div class="mt-1 inline-block max-w-full rounded-2xl px-2 py-1.5 text-xs leading-snug break-words shadow-sm <?php echo e($mine ? 'bg-emerald-500 text-white rounded-br-sm' : 'bg-gray-100 text-gray-800 rounded-bl-sm'); ?>">
                <?php if(trim((string) $c->body) !== ''): ?>
                  <div class="whitespace-pre-wrap break-words"><?php echo e($c->body); ?></div>
                <?php endif; ?>
                <?php if($c->attachment): ?>
                  <?php
                    $isCommentImage = (bool) preg_match('/\.(jpe?g|png|gif|webp)$/i', $c->attachment);
                  ?>
                  <?php if($isCommentImage): ?>
                    <?php $commentImgUrl = route('comment.download', $c->id) . '?inline=1'; ?>
                    <div class="mt-2 overflow-hidden rounded-lg">
                      <img src="<?php echo e($commentImgUrl); ?>" alt="Gambar — klik untuk memperbesar" title="Klik untuk pratinjau" data-full-src="<?php echo e($commentImgUrl); ?>" role="button" tabindex="0" class="js-comment-image-preview max-h-72 w-full max-w-full cursor-zoom-in object-contain transition-opacity hover:opacity-95 <?php echo e($mine ? 'ring-1 ring-white/30' : 'ring-1 ring-gray-200/80'); ?>" loading="lazy" decoding="async" />
                    </div>
                    <div class="mt-1.5">
                      <a href="<?php echo e(route('comment.download', $c->id)); ?>" class="inline-flex items-center text-[10px] font-medium underline <?php echo e($mine ? 'text-white/90 hover:text-white' : 'text-indigo-600 hover:text-indigo-800'); ?>">Unduh gambar</a>
                    </div>
                  <?php else: ?>
                    <div class="mt-2">
                      <a href="<?php echo e(route('comment.download', $c->id)); ?>?inline=1" target="_blank" rel="noopener" class="inline-flex items-center px-2 py-1 rounded-md ring-1 ring-white/40 text-xs <?php echo e($mine ? 'bg-white/10 text-white hover:bg-white/20' : 'bg-white text-indigo-600 hover:bg-indigo-50 ring-indigo-200'); ?>">Lampiran</a>
                    </div>
                  <?php endif; ?>
                <?php endif; ?>
              </div>
              <div class="mt-1 flex <?php echo e($mine ? 'justify-end' : 'justify-start'); ?>">
                <?php if(auth()->guard()->check()): ?>
                  <?php if($ticket->status !== 'CLOSED' && auth()->id() === $c->user_id): ?>
                    <form method="POST" action="<?php echo e(route('comment.delete', $c->id)); ?>" onsubmit="return confirm('Hapus komentar ini?')">
                      <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                      <button class="text-[10px] text-red-500/80 hover:text-red-600 hover:underline">Hapus</button>
                    </form>
                  <?php endif; ?>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
          <div class="text-gray-500 text-sm">Belum ada komentar.</div>
        <?php endif; ?>
      </div>

      <div class="shrink-0 mt-3 border-t pt-3">
        <?php if($ticket->status !== 'CLOSED'): ?>
          <form action="<?php echo e(route('ticket.comment', $ticket->id)); ?>" method="POST" enctype="multipart/form-data" id="ticket-comment-form" class="flex flex-col gap-2">
            <?php echo csrf_field(); ?>
            <div id="comment-attachment-preview" class="hidden flex flex-wrap items-start gap-2 rounded-lg border border-emerald-100 bg-emerald-50/50 p-2">
              <img id="comment-attachment-preview-img" alt="Pratinjau lampiran — klik untuk memperbesar" title="Klik untuk pratinjau" class="hidden max-h-40 w-auto max-w-full cursor-zoom-in rounded-md object-contain ring-1 ring-gray-200/80 transition-opacity hover:opacity-90" />
              <span id="comment-attachment-preview-file" class="hidden max-w-full break-all text-xs text-gray-700"></span>
              <button type="button" id="comment-attachment-clear" class="ml-auto text-xs font-medium text-red-600 hover:text-red-700 hover:underline">Hapus lampiran</button>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end">
              <textarea id="commentBody" name="body" class="w-full flex-1 rounded-lg border-gray-300 resize-y min-h-[44px] max-h-[160px] sm:min-h-[38px] sm:max-h-[120px]" rows="2" placeholder="Tulis pesan / Tempel gambar di sini (Ctrl+V) ."></textarea>
              <div class="flex shrink-0 items-center justify-end gap-2 self-end sm:self-auto">
                <label id="attachBtn" class="inline-flex items-center justify-center w-10 h-10 rounded-full ring-1 ring-gray-200 bg-white hover:bg-gray-50 text-gray-600 cursor-pointer transition-colors" title="Lampirkan file">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79V7a5 5 0 00-9.9-1M3 13l7.5-7.5a3.5 3.5 0 015 5L9 19a4 4 0 11-5.657-5.657L14 2" />
                  </svg>
                  <input id="attachInput" type="file" name="attachment" class="sr-only" />
                </label>
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg">Kirim</button>
              </div>
            </div>
          </form>
        <?php else: ?>
          <div class="rounded-lg bg-gray-50 text-gray-600 ring-1 ring-gray-200 px-4 py-3 text-sm">Tiket telah ditutup. Komentar dan lampiran dinonaktifkan.</div>
        <?php endif; ?>
      </div>
    </div>
  </aside>
</div>
</div>


<div id="comment-image-lightbox" class="fixed inset-0 z-[130] hidden bg-black/80 p-3 sm:p-6" role="dialog" aria-modal="true" aria-labelledby="comment-image-lightbox-title" aria-hidden="true">
  <p id="comment-image-lightbox-title" class="sr-only">Pratinjau gambar</p>
  <button type="button" id="comment-image-lightbox-backdrop" class="absolute inset-0 cursor-default" aria-label="Tutup pratinjau"></button>
  <div class="relative z-10 flex max-h-full max-w-full flex-col items-center">
    <button type="button" id="comment-image-lightbox-close" class="mb-2 shrink-0 rounded-full bg-white/95 px-4 py-1.5 text-sm font-medium text-gray-800 shadow-md ring-1 ring-gray-200 hover:bg-white sm:absolute sm:-top-3 sm:right-0 sm:mb-0">Tutup</button>
    <img id="comment-image-lightbox-img" src="" alt="Pratinjau gambar" class="max-h-[min(85dvh,900px)] max-w-full cursor-default rounded-lg object-contain shadow-2xl ring-1 ring-white/10" width="1200" height="900" decoding="async" />
  </div>
</div>


<div x-data="{ open:false }"
  x-on:open-history.window="open=true"
  x-show="open" x-cloak
  class="fixed inset-0 z-[110] flex items-start sm:items-center justify-center"
     role="dialog" aria-modal="true" aria-label="Riwayat tiket"
     @keydown.escape.window="open=false">

  
  <div class="absolute inset-0 bg-black/10 backdrop-blur-sm" x-transition.opacity @click="open=false" aria-hidden="true"></div>

  
  <div id="history-panel" class="relative w-full max-w-md sm:max-w-lg md:max-w-xl lg:max-w-3xl xl:max-w-4xl mx-auto mx-4 mt-4 sm:mt-0 rounded-2xl bg-white shadow-xl p-3 sm:p-5 text-xs sm:text-sm"
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
       x-transition:enter-end="opacity-100 scale-100 translate-y-0"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100 scale-100 translate-y-0"
       x-transition:leave-end="opacity-0 scale-95 -translate-y-1">

    <div class="flex items-start justify-between mb-4">
      <h3 class="text-lg font-semibold text-gray-800">History Tiket</h3>
      <div class="flex items-center gap-2">
        <button type="button" class="px-3 h-8 inline-flex items-center rounded-lg text-sm text-indigo-600 ring-1 ring-indigo-200 hover:bg-indigo-50" onclick="window.downloadHistoryPanel && window.downloadHistoryPanel()">Download</button>
        <button class="h-8 w-8 inline-flex items-center justify-center rounded-md hover:bg-gray-100" @click="open=false" aria-label="Tutup">✕</button>
      </div>
    </div>

    <ul id="history-list" class="tl space-y-5 max-h-80 overflow-auto pr-1">
      <?php
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
      ?>

      <?php $__empty_1 = true; $__currentLoopData = $ticket->histories->sortBy('created_at'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
          $dot = $colors[$h->action] ?? '#4b5563';
          $label = $labels[$h->action] ?? ucfirst(str_replace('_',' ', $h->action));
          $meta = $h->meta ?? [];
        ?>
        <li id="h-<?php echo e($h->id); ?>" class="tl-item" style="--dot: <?php echo e($dot); ?>; --accent: <?php echo e($dot); ?>" data-history-ts="<?php echo e(optional($h->created_at)->format('c')); ?>">
          <div class="tl-card rounded-xl border border-gray-200 bg-white p-4 shadow-md">
            <div class="flex items-center justify-between gap-3">
              <span class="tl-label"><?php echo e($label); ?></span>
              <div class="shrink-0 text-xs text-gray-500"><?php echo e(optional($h->created_at)->format('d M Y H:i') ?? '-'); ?></div>
            </div>
            <div class="mt-1 text-xs text-gray-500">Oleh: <?php echo e(optional($h->user)->name ?? '-'); ?></div>
            <?php if($h->action === 'assigned_vendor' && (!empty($meta['vendor_name']) || !empty($meta['vendor_id']))): ?>
              <div class="mt-2 inline-flex items-center rounded-full bg-fuchsia-50 px-2 py-0.5 text-[11px] font-medium text-fuchsia-700 ring-1 ring-fuchsia-100">
                <?php echo e($meta['vendor_name'] ?? ('Vendor ID '.$meta['vendor_id'])); ?>

              </div>
            <?php endif; ?>
            <?php if($h->note): ?>
              <div class="mt-2 text-sm text-gray-800 whitespace-pre-line"><?php echo e($h->note); ?></div>
            <?php endif; ?>
          </div>
        </li>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <li class="tl-item" style="--dot:#6b7280; --accent:#6b7280">
          <div class="tl-card rounded-xl border border-gray-200 bg-white p-4 shadow-md">
            <div class="text-sm text-gray-600">Belum ada log.</div>
          </div>
        </li>
      <?php endif; ?>
    </ul>
  </div>
</div>



<div x-data="{ open:false }"
  x-on:open-update.window="open=true"
  x-show="open" x-cloak
  class="fixed inset-0 z-[120] overflow-y-auto overflow-x-hidden"
     role="dialog" aria-modal="true" aria-labelledby="modal-update-title"
     @keydown.escape.window="open=false">

  <div class="fixed inset-0 z-0 bg-black/40 backdrop-blur-[2px]" x-transition.opacity @click="open=false" aria-hidden="true"></div>

  <div class="relative z-10 flex min-h-[100dvh] w-full items-center justify-center px-3 py-4 sm:px-4 sm:py-6" @click.self="open=false">
  <div class="relative flex w-full max-w-2xl max-h-[calc(100dvh-1rem)] flex-col overflow-hidden rounded-2xl bg-white text-sm shadow-2xl ring-1 ring-gray-200/80 md:max-h-[calc(100dvh-1.25rem)] md:max-w-4xl"
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
       x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
       x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

    <div class="flex shrink-0 items-center justify-between gap-2 border-b border-gray-100 bg-gray-50/80 px-3 py-2 sm:px-4">
      <div class="min-w-0">
        <h3 id="modal-update-title" class="truncate text-sm font-semibold text-gray-900 sm:text-base">Update tiket</h3>
        <p class="truncate text-[11px] text-gray-500 sm:text-xs"><?php echo e($ticket->nomor_tiket ?? 'Tiket'); ?></p>
      </div>
      <button type="button" class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-200/80 hover:text-gray-800 sm:h-9 sm:w-9" @click="open=false" aria-label="Tutup">
        <span class="text-lg leading-none" aria-hidden="true">×</span>
      </button>
    </div>

    <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-3 py-3 sm:px-4 sm:py-3">
    <?php if(auth()->guard()->check()): ?>
      <?php if(auth()->user()->role === 'IT'): ?>
        <div class="flex flex-col gap-3 md:flex-row md:items-stretch md:gap-4">
          <div class="flex min-w-0 flex-col gap-3 md:basis-0 md:min-w-0 md:flex-1">
          <section class="rounded-lg border border-gray-100 bg-white p-3 shadow-sm ring-1 ring-gray-50">
            <h4 class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Status tiket</h4>
            <form method="POST" action="<?php echo e(route('it.ticket.status', $ticket->id)); ?>" class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-center">
              <?php echo csrf_field(); ?>
              <div class="min-w-0 flex-1">
                <label for="modal-status-select" class="sr-only">Status</label>
                <select id="modal-status-select" name="status" class="h-9 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                  <?php $__currentLoopData = ($statuses ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($s); ?>" <?php if($ticket->status === $s): echo 'selected'; endif; ?>><?php echo e($s); ?></option>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
              </div>
              <button type="submit" class="inline-flex h-9 shrink-0 items-center justify-center rounded-lg bg-indigo-600 px-3 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 sm:min-w-[7.5rem]">Ubah status</button>
            </form>
          </section>

          <section class="rounded-lg border border-gray-100 bg-white p-3 shadow-sm ring-1 ring-gray-50">
            <h4 class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Assign ke vendor</h4>
            <form method="POST" action="<?php echo e(route('it.ticket.assign_vendor', $ticket->id)); ?>" class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-center">
              <?php echo csrf_field(); ?>
              <div class="min-w-0 flex-1">
                <label for="modal-vendor-select" class="sr-only">Vendor</label>
                <select id="modal-vendor-select" name="vendor_id" class="h-9 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                  <option value="" <?php echo e(empty($ticket->vendor_id) ? 'selected' : ''); ?>>Tidak assign vendor</option>
                  <?php $__currentLoopData = ($vendors ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($v->id); ?>" <?php if($ticket->vendor_id === $v->id): echo 'selected'; endif; ?>><?php echo e($v->name); ?></option>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
              </div>
              <button type="submit" class="inline-flex h-9 shrink-0 items-center justify-center rounded-lg bg-emerald-600 px-3 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 sm:min-w-[7.5rem]">Simpan assign</button>
            </form>
          </section>

          <section class="rounded-lg border border-gray-100 bg-white p-3 shadow-sm ring-1 ring-gray-50">
            <h4 class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Override kategori</h4>
            <form method="POST" action="<?php echo e(route('it.ticket.override_category', $ticket->id)); ?>" class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-2">
              <?php echo csrf_field(); ?>
              <div class="min-w-0 sm:col-span-1">
                <label for="override-category-select" class="mb-0.5 block text-[11px] font-medium text-gray-600">Kategori</label>
                <select id="override-category-select" name="category_id" class="h-9 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                  <option value="">— Tidak diubah —</option>
                  <?php $__currentLoopData = ($categories ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($c->id); ?>" <?php if($ticket->category_id == $c->id): echo 'selected'; endif; ?>><?php echo e($c->name); ?></option>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
              </div>
              <div class="min-w-0 sm:col-span-1">
                <label for="override-subcategory-select" class="mb-0.5 block text-[11px] font-medium text-gray-600">Subkategori</label>
                <select id="override-subcategory-select" name="subcategory_id" class="h-9 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                  <option value="">— Pilih subkategori —</option>
                </select>
              </div>
              <div class="flex flex-col-reverse gap-2 border-t border-gray-100 pt-2 sm:col-span-2 sm:flex-row sm:justify-end">
                <button type="button" class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-200 bg-white px-3 text-sm font-medium text-gray-700 hover:bg-gray-50" @click="open=false">Batal</button>
                <button type="submit" class="inline-flex h-9 items-center justify-center rounded-lg bg-indigo-600 px-3 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">Simpan kategori</button>
              </div>
            </form>
          </section>
          </div>

          <div class="flex min-w-0 flex-col gap-3 md:basis-0 md:min-w-0 md:flex-1">
          <?php if(!empty($ticket->vendor_followup)): ?>
            <section class="max-h-32 overflow-y-auto rounded-lg border border-fuchsia-100 bg-fuchsia-50/40 p-3">
              <h4 class="text-[11px] font-semibold uppercase tracking-wide text-fuchsia-800">Tindak lanjut vendor</h4>
              <div class="mt-1 text-xs text-gray-800 whitespace-pre-line"><?php echo e($ticket->vendor_followup); ?></div>
              <?php if($ticket->vendor_followup_at): ?>
                <div class="mt-1 text-[10px] text-fuchsia-900/70">Diperbarui: <?php echo e(optional($ticket->vendor_followup_at)->format('d M Y H:i') ?? '-'); ?></div>
              <?php endif; ?>
            </section>
          <?php endif; ?>

          <section class="rounded-lg border border-red-100 bg-red-50/30 p-3 ring-1 ring-red-100/80">
            <h4 class="text-[11px] font-semibold uppercase tracking-wide text-red-800">Tutup tiket</h4>
            <p class="mt-0.5 text-[11px] text-red-900/70">Pilih <strong>root cause</strong>, lalu <strong>detail root cause</strong> (radio dari Parameter). <strong>Closed note</strong> = catatan penutupan / isian untuk opsi Lainnya.</p>
            <form method="POST" action="<?php echo e(route('it.ticket.close', $ticket->id)); ?>" id="form-close-ticket" class="mt-2 space-y-2">
              <?php echo csrf_field(); ?>
              <div>
                <label for="modal-close-root-cause" class="mb-0.5 block text-[11px] font-medium text-gray-700">Root cause</label>
                <select id="modal-close-root-cause" name="root_cause" required class="h-9 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                  <?php $rcOld = old('root_cause', $ticket->root_cause); ?>
                  <option value="" disabled <?php echo e(empty($rcOld) ? 'selected' : ''); ?>>Pilih root cause</option>
                  <?php $__currentLoopData = ($rootCauses ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($rc->name); ?>" <?php if($rcOld === $rc->name): echo 'selected'; endif; ?>><?php echo e($rc->name); ?></option>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
              </div>
              <div id="root-cause-detail-radios-wrap" class="min-h-[2.5rem]"></div>
              <div id="modal-close-note-wrap">
                <label id="modal-close-note-label" for="modal-close-note" class="mb-0.5 block text-[11px] font-medium text-gray-700">Closed note</label>
                <textarea id="modal-close-note" name="closed_note" rows="2" class="max-h-28 w-full resize-y rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="…"><?php echo e(old('closed_note')); ?></textarea>
              </div>
              <div class="flex flex-col-reverse gap-2 border-t border-red-100/80 pt-2 sm:flex-row sm:justify-end">
                <button type="button" class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-200 bg-white px-3 text-sm font-medium text-gray-700 hover:bg-gray-50" @click="open=false">Batal</button>
                <button type="submit" class="inline-flex h-9 items-center justify-center rounded-lg bg-red-600 px-3 text-sm font-medium text-white shadow-sm hover:bg-red-700">Close ticket</button>
              </div>
            </form>
          </section>
          </div>
        </div>
      <?php elseif(auth()->user()->role === 'VENDOR' && $ticket->vendor_id === auth()->id()): ?>
        <form method="POST" action="<?php echo e(route('vendor.ticket.followup', $ticket->id)); ?>" class="space-y-3 rounded-lg border border-gray-100 bg-white p-3 shadow-sm ring-1 ring-gray-50">
          <?php echo csrf_field(); ?>
          <h4 class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Tindak lanjut vendor</h4>
          <div>
            <label for="modal-vendor-followup" class="mb-0.5 block text-[11px] font-medium text-gray-700">Catatan</label>
            <textarea id="modal-vendor-followup" name="vendor_followup" rows="3" required class="max-h-40 w-full resize-y rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Catat tindak lanjut…"><?php echo e(old('vendor_followup', $ticket->vendor_followup)); ?></textarea>
          </div>
          <div class="flex flex-col-reverse gap-2 border-t border-gray-100 pt-2 sm:flex-row sm:justify-end">
            <button type="button" class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-200 bg-white px-3 text-sm font-medium text-gray-700 hover:bg-gray-50" @click="open=false">Batal</button>
            <button type="submit" class="inline-flex h-9 items-center justify-center rounded-lg bg-emerald-600 px-3 text-sm font-medium text-white shadow-sm hover:bg-emerald-700">Simpan</button>
          </div>
        </form>
      <?php endif; ?>
    <?php endif; ?>
    </div>

  </div>
  </div>
</div>

  
  <?php $__env->startPush('scripts'); ?>
  <script>
    window.rootCauseDetailsByRoot = <?php echo json_encode($rootCauseDetailsByRootName ?? [], 15, 512) ?>;
    window.selectedRootCauseDetailId = <?php echo json_encode(old('root_cause_detail_id', $ticket->root_cause_detail_id), 512) ?>;
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
        link.download = 'history_<?php echo e(Str::slug($ticket->nomor_tiket ?? "tiket")); ?>.png';
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
      // Lampiran + tempel gambar (clipboard) seperti WhatsApp
      const attachInput = document.getElementById('attachInput');
      const attachBtn = document.getElementById('attachBtn');
      const commentBodyEl = document.getElementById('commentBody');
      const previewWrap = document.getElementById('comment-attachment-preview');
      const previewImg = document.getElementById('comment-attachment-preview-img');
      const previewFile = document.getElementById('comment-attachment-preview-file');
      const previewClear = document.getElementById('comment-attachment-clear');
      let pastePreviewObjectUrl = null;

      const revokePastePreview = () => {
        if (pastePreviewObjectUrl) {
          URL.revokeObjectURL(pastePreviewObjectUrl);
          pastePreviewObjectUrl = null;
        }
      };

      const setAttachBtnActive = (on) => {
        if (!attachBtn) return;
        attachBtn.classList.toggle('ring-emerald-300', on);
        attachBtn.classList.toggle('bg-emerald-50', on);
        attachBtn.classList.toggle('text-emerald-700', on);
        attachBtn.classList.toggle('hover:bg-emerald-100', on);
        attachBtn.classList.toggle('ring-gray-200', !on);
        attachBtn.classList.toggle('bg-white', !on);
        attachBtn.classList.toggle('text-gray-600', !on);
        attachBtn.classList.toggle('hover:bg-gray-50', !on);
      };

      const isImageFile = (f) => f && f.type && f.type.indexOf('image/') === 0;

      const syncAttachmentPreview = () => {
        revokePastePreview();
        if (!previewWrap || !previewImg || !previewFile || !attachInput) return;
        const f = attachInput.files && attachInput.files[0] ? attachInput.files[0] : null;
        if (!f) {
          previewWrap.classList.add('hidden');
          previewImg.classList.add('hidden');
          previewImg.removeAttribute('src');
          previewFile.classList.add('hidden');
          previewFile.textContent = '';
          setAttachBtnActive(false);
          return;
        }
        previewWrap.classList.remove('hidden');
        setAttachBtnActive(true);
        if (isImageFile(f)) {
          previewFile.classList.add('hidden');
          previewFile.textContent = '';
          pastePreviewObjectUrl = URL.createObjectURL(f);
          previewImg.src = pastePreviewObjectUrl;
          previewImg.classList.remove('hidden');
        } else {
          previewImg.classList.add('hidden');
          previewImg.removeAttribute('src');
          previewFile.textContent = f.name || 'Berkas terpilih';
          previewFile.classList.remove('hidden');
        }
      };

      const clearCommentAttachment = () => {
        if (attachInput) attachInput.value = '';
        syncAttachmentPreview();
      };

      if (attachInput && attachBtn) {
        attachInput.addEventListener('change', syncAttachmentPreview);
      }
      if (previewClear) {
        previewClear.addEventListener('click', clearCommentAttachment);
      }

      if (commentBodyEl && attachInput) {
        commentBodyEl.addEventListener('paste', (e) => {
          const items = e.clipboardData && e.clipboardData.items;
          if (!items || !items.length) return;
          for (let i = 0; i < items.length; i++) {
            const it = items[i];
            if (it.kind === 'file' && it.type && it.type.indexOf('image/') === 0) {
              const blob = it.getAsFile();
              if (!blob) continue;
              e.preventDefault();
              const ext = (blob.type === 'image/png') ? 'png' : (blob.type === 'image/gif') ? 'gif' : (blob.type === 'image/webp') ? 'webp' : 'jpg';
              const file = new File([blob], 'tempel-gambar.' + ext, { type: blob.type || 'image/png' });
              const dt = new DataTransfer();
              dt.items.add(file);
              attachInput.files = dt.files;
              syncAttachmentPreview();
              break;
            }
          }
        });
      }

      // Pratinjau gambar komentar (modal lightbox)
      const commentImageLightbox = document.getElementById('comment-image-lightbox');
      const commentImageLightboxImg = document.getElementById('comment-image-lightbox-img');
      const commentImageLightboxBackdrop = document.getElementById('comment-image-lightbox-backdrop');
      const commentImageLightboxClose = document.getElementById('comment-image-lightbox-close');

      const openCommentImageLightbox = (src) => {
        if (!commentImageLightbox || !commentImageLightboxImg || !src) return;
        commentImageLightboxImg.src = src;
        commentImageLightbox.classList.remove('hidden');
        commentImageLightbox.classList.add('flex', 'flex-col', 'items-center', 'justify-center');
        commentImageLightbox.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        window.setTimeout(() => commentImageLightboxClose?.focus(), 0);
      };

      const closeCommentImageLightbox = () => {
        if (!commentImageLightbox || !commentImageLightboxImg) return;
        commentImageLightbox.classList.add('hidden');
        commentImageLightbox.classList.remove('flex', 'flex-col', 'items-center', 'justify-center');
        commentImageLightbox.setAttribute('aria-hidden', 'true');
        commentImageLightboxImg.removeAttribute('src');
        document.body.style.overflow = '';
      };

      if (list) {
        list.addEventListener('click', (ev) => {
          const el = ev.target.closest('.js-comment-image-preview');
          if (!el) return;
          ev.preventDefault();
          const src = el.getAttribute('data-full-src') || el.currentSrc || el.src;
          openCommentImageLightbox(src);
        });
        list.addEventListener('keydown', (ev) => {
          if (ev.key !== 'Enter' && ev.key !== ' ') return;
          const el = ev.target.closest('.js-comment-image-preview');
          if (!el) return;
          ev.preventDefault();
          const src = el.getAttribute('data-full-src') || el.currentSrc || el.src;
          openCommentImageLightbox(src);
        });
      }

      if (previewImg) {
        previewImg.addEventListener('click', () => {
          if (previewImg.classList.contains('hidden') || !previewImg.getAttribute('src')) return;
          openCommentImageLightbox(previewImg.src);
        });
      }

      commentImageLightboxBackdrop?.addEventListener('click', closeCommentImageLightbox);
      commentImageLightboxClose?.addEventListener('click', closeCommentImageLightbox);
      document.addEventListener('keydown', (ev) => {
        if (ev.key !== 'Escape') return;
        if (!commentImageLightbox || commentImageLightbox.classList.contains('hidden')) return;
        closeCommentImageLightbox();
      });

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
      const ticketId = <?php echo e((int) $ticket->id); ?>;
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
      const composer = document.getElementById('commentBody') || document.querySelector('textarea[name="body"]');
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
      if (form) {
        form.addEventListener('submit', (ev) => {
          const ta = document.getElementById('commentBody') || form.querySelector('[name="body"]');
          const txt = (ta && ta.value) ? ta.value.trim() : '';
          const hasFile = attachInput && attachInput.files && attachInput.files.length > 0;
          if (!txt && !hasFile) {
            ev.preventDefault();
            alert('Tulis pesan atau tempel/lampirkan gambar terlebih dahulu.');
            if (ta) ta.focus();
            return;
          }
          markCommentsSeen();
        });
      }

      // Mark history seen when opening the history modal (listen to Alpine custom event)
      window.addEventListener('open-history', markHistorySeen);

      // ===== Override kategori/subkategori (IT modal) =====
      try{
        const overrideCategorySelect = document.getElementById('override-category-select');
        const overrideSubcategorySelect = document.getElementById('override-subcategory-select');
        const categoryBaseUrl = '<?php echo e(url('/categories')); ?>';
        const initCat = '<?php echo e($ticket->category_id ?? ''); ?>';
        const initSub = '<?php echo e($ticket->subcategory_id ?? ''); ?>';

        async function loadOverrideSubcategories(categoryId, toSelect = null){
          if(!overrideSubcategorySelect) return;
          overrideSubcategorySelect.innerHTML = '<option value="">-- Pilih Subkategori --</option>';
          if(!categoryId) return;
          try{
            const res = await fetch(`${categoryBaseUrl}/${categoryId}/subcategories`);
            if(!res.ok){ console.error('Gagal memuat subkategori', res.status); return; }
            const data = await res.json();
            if(!Array.isArray(data) || data.length === 0){
              const opt = document.createElement('option'); opt.value = ''; opt.textContent = '— Tidak ada subkategori —'; overrideSubcategorySelect.appendChild(opt); return;
            }
            data.forEach(s => {
              const opt = document.createElement('option'); opt.value = s.id; opt.textContent = s.name; overrideSubcategorySelect.appendChild(opt);
            });
            if(toSelect) overrideSubcategorySelect.value = toSelect;
          }catch(err){ console.error('Error saat memuat subkategori', err); }
        }

        if(overrideCategorySelect){
          overrideCategorySelect.addEventListener('change', function(){ loadOverrideSubcategories(this.value, null); });
        }
        if(initCat){ loadOverrideSubcategories(initCat, initSub); }
        }catch(_){ }

      // Penutupan tiket: radio detail root cause per root cause (+ Lainnya + closed note)
      try {
        const byRoot = window.rootCauseDetailsByRoot || {};
        const rootSel = document.getElementById('modal-close-root-cause');
        const folWrap = document.getElementById('root-cause-detail-radios-wrap');
        const noteTa = document.getElementById('modal-close-note');
        const noteLbl = document.getElementById('modal-close-note-label');
        if (!rootSel || !folWrap || !noteTa || !noteLbl) {
          // bukan IT / elemen tidak ada
        } else {
          function syncNoteForFollowup() {
            const list = Array.isArray(byRoot[rootSel.value]) ? byRoot[rootSel.value] : [];
            if (list.length === 0) {
              noteLbl.textContent = 'Closed note';
              noteTa.required = true;
              noteTa.placeholder = 'Wajib, minimal 3 karakter…';
              return;
            }
            const picked = folWrap.querySelector('input[name="root_cause_detail_id"]:checked');
            if (!picked) {
              noteLbl.textContent = 'Closed note (opsional)';
              noteTa.required = false;
              noteTa.placeholder = 'Opsional…';
              return;
            }
            const opt = list.find((o) => String(o.id) === picked.value);
            if (opt && opt.is_other) {
              noteLbl.textContent = 'Closed note (wajib untuk Lainnya)';
              noteTa.required = true;
              noteTa.placeholder = 'Isi closed note…';
            } else {
              noteLbl.textContent = 'Closed note (opsional)';
              noteTa.required = false;
              noteTa.placeholder = 'Opsional — ditambahkan setelah label detail';
            }
          }

          function renderDetailRadios() {
            const name = rootSel.value;
            const list = Array.isArray(byRoot[name]) ? byRoot[name] : [];
            folWrap.innerHTML = '';
            if (!name) {
              noteTa.required = false;
              noteLbl.textContent = 'Closed note';
              return;
            }
            if (list.length === 0) {
              folWrap.innerHTML = '<p class="text-[11px] leading-snug text-amber-900">Belum ada detail untuk root cause ini. Atur di <strong>Parameter → Detail root cause</strong> atau isi <strong>closed note</strong> saja (wajib).</p>';
              noteTa.required = true;
              noteLbl.textContent = 'Closed note';
              noteTa.placeholder = 'Wajib, minimal 3 karakter…';
              return;
            }

            const fs = document.createElement('fieldset');
            fs.className = 'rounded-md border border-red-200/70 bg-white/70 p-2';
            const leg = document.createElement('legend');
            leg.className = 'px-1 text-[10px] font-semibold uppercase tracking-wide text-red-900';
            leg.textContent = 'Detail root cause';
            fs.appendChild(leg);
            const selRaw = window.selectedRootCauseDetailId;
            const selId = selRaw != null && selRaw !== '' ? String(selRaw) : '';
            list.forEach((opt, idx) => {
              const lab = document.createElement('label');
              lab.className = 'flex cursor-pointer items-start gap-2 rounded px-1 py-1 text-xs text-gray-800 hover:bg-red-50/60';
              const inp = document.createElement('input');
              inp.type = 'radio';
              inp.name = 'root_cause_detail_id';
              inp.value = String(opt.id);
              inp.className = 'mt-0.5 h-3.5 w-3.5 shrink-0 border-gray-300 text-red-600 focus:ring-red-500';
              if (idx === 0) inp.required = true;
              if (selId ? String(opt.id) === selId : idx === 0) inp.checked = true;
              inp.setAttribute('data-is-other', opt.is_other ? '1' : '0');
              lab.appendChild(inp);
              const span = document.createElement('span');
              span.textContent = opt.label;
              lab.appendChild(span);
              fs.appendChild(lab);
            });
            folWrap.appendChild(fs);
            fs.addEventListener('change', syncNoteForFollowup);
            syncNoteForFollowup();
          }

          rootSel.addEventListener('change', renderDetailRadios);
          renderDetailRadios();
        }
      } catch (_) {}
    });
  </script>
  <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\helpdesk-app\resources\views/tickets/show.blade.php ENDPATH**/ ?>