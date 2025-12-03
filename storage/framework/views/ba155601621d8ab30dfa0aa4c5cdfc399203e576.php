
<?php $__env->startSection('title','Daftar Tiket'); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
  
  <div class="flex flex-col gap-3 md:gap-4">
    <div class="flex items-center justify-center text-center">
      <div>
        <h2 class="text-lg font-semibold text-gray-800">Daftar Tiket</h2>
        <p class="text-sm text-gray-500">Klik nomor tiket untuk melihat detail.</p>
      </div>
    </div>

    
    <form method="GET" class="w-full flex flex-col md:flex-row items-end justify-center gap-2 md:gap-2 md:flex-nowrap overflow-x-auto mb-4 md:mb-6" id="filter-form">
      <div class="order-1 md:order-none shrink-0 w-full md:w-[360px]">
        <input type="text" name="q" value="<?php echo e(request('q')); ?>" placeholder="Cari nomor / deskripsi / kategori"
               class="w-full h-10 rounded-lg border-gray-300 px-3 focus:border-indigo-500 focus:ring-indigo-500" />
      </div>

      
      <div class="order-4 md:order-none shrink-0 w-full md:w-[150px]">
        <label class="sr-only">Status</label>
        <select name="status" class="w-full h-10 rounded-lg border-gray-300 px-3 focus:border-indigo-500 focus:ring-indigo-500">
          <option value="">Status</option>
          <?php $__currentLoopData = ['OPEN','ON_PROGRESS','ESKALASI_VENDOR','VENDOR_RESOLVED','CLOSED']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($s); ?>" <?php if(request('status')===$s): echo 'selected'; endif; ?>><?php echo e($s); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>

      
      <div class="shrink-0 flex flex-col md:flex-row gap-2 w-full md:w-auto">
        <div class="order-2 md:order-none shrink-0 w-full md:w-[150px]">
          <input type="text" name="date_from" value="<?php echo e(request('date_from')); ?>" placeholder="Tgl Awal" inputmode="numeric" pattern="\d{4}-\d{2}-\d{2}"
                 class="w-full h-10 rounded-lg border-gray-300 px-3 focus:border-indigo-500 focus:ring-indigo-500"
                 onfocus="this.type='date'" onblur="if(!this.value) this.type='text'" />
        </div>
        <div class="order-3 md:order-none shrink-0 w-full md:w-[150px]">
          <input type="text" name="date_to" value="<?php echo e(request('date_to')); ?>" placeholder="Tgl Akhir" inputmode="numeric" pattern="\d{4}-\d{2}-\d{2}"
                 class="w-full h-10 rounded-lg border-gray-300 px-3 focus:border-indigo-500 focus:ring-indigo-500"
                 onfocus="this.type='date'" onblur="if(!this.value) this.type='text'" />
        </div>
      </div>

      
      <div class="order-5 md:order-none shrink-0 flex gap-2 justify-center w-full md:w-auto">
        <button type="submit" class="w-full md:w-auto h-10 rounded-lg bg-gradient-to-r from-blue-500 to-sky-500 text-white px-4">Filter</button>

        <?php if(request()->hasAny(['q','status','date_from','date_to'])): ?>
          <a href="<?php echo e(route('it.dashboard')); ?>"
             class="shrink-0 h-10 inline-block text-center rounded-lg border border-gray-200 px-4 text-sm text-gray-700 hover:underline leading-10">
             Reset
           </a>
            <a href="<?php echo e(route('it.tickets.export', request()->query())); ?>"
              class="shrink-0 h-10 inline-block text-center rounded-lg bg-emerald-600 px-4 text-white hover:bg-emerald-700 leading-10">
             Export Result
           </a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  
  <div class="hidden md:block overflow-x-auto">
    <table class="min-w-full text-sm table-fixed">
      <colgroup>
        <col style="width:4%">
        <col style="width:18%">
        <col style="width:18%">
        <col style="width:20%">
        <col style="width:12%">
        <col style="width:18%">
        <col style="width:10%">
      </colgroup>
      <thead class="bg-gray-50 text-gray-600">
        <tr>
          <th class="py-3 px-4 text-left whitespace-nowrap">#</th>
          <th class="py-3 px-4 text-left whitespace-nowrap">Nomor</th>
          <th class="py-3 px-4 text-left whitespace-nowrap">Kategori</th>
          <th class="py-3 px-4 text-left whitespace-nowrap">Pembuat</th>
          <th class="py-3 px-4 text-left whitespace-nowrap">Status</th>
          <th class="py-3 px-4 text-left whitespace-nowrap">IT Handler</th>
          <th class="py-3 px-4 text-left whitespace-nowrap">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr class="hover:bg-gray-50">
          <td class="py-3 px-4 text-gray-500"><?php echo e($tickets->firstItem()+$i); ?></td>
          <td class="py-3 px-4 font-medium truncate">
            <a href="<?php echo e(route('ticket.show',$t->id)); ?>" class="text-indigo-600 hover:underline block truncate"><?php echo e($t->nomor_tiket); ?></a>
          </td>
          <td class="py-3 px-4 truncate"><?php echo e($t->kategori); ?></td>
          <td class="py-3 px-4 truncate"><?php echo e($t->user->name ?? '-'); ?></td>
          <td class="py-3 px-4">
            <?php
              $badge = match($t->status){
                'OPEN'             => 'bg-gray-100 text-gray-700 ring-gray-200',
                'ON_PROGRESS'      => 'bg-amber-100 text-amber-800 ring-amber-200',
                'ESKALASI_VENDOR'  => 'bg-fuchsia-100 text-fuchsia-800 ring-fuchsia-200',
                'VENDOR_RESOLVED'  => 'bg-indigo-100 text-indigo-800 ring-indigo-200',
                'CLOSED'           => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
                default            => 'bg-gray-100 text-gray-700 ring-gray-200',
              };
            ?>
            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 <?php echo e($badge); ?>"><?php echo e($t->status); ?></span>
          </td>
          <td class="py-3 px-4 truncate"><?php echo e($t->it->name ?? '-'); ?></td>
          <td class="py-3 px-4 space-x-1 whitespace-nowrap">
            <a href="<?php echo e(route('ticket.show',$t->id)); ?>" class="inline-flex items-center rounded-lg bg-gradient-to-r from-blue-500 to-sky-500 px-3 py-1.5 text-tulisan-50 hover:bg-gray-200">Detail</a>
            <?php if($t->status==='OPEN' || ($t->status!=='CLOSED' && !$t->it_id)): ?>
              <form method="POST" class="inline" action="<?php echo e(route('it.ticket.take',$t->id)); ?>"><?php echo csrf_field(); ?>
                <button class="rounded-lg bg-indigo-600 px-3 py-1.5 text-white hover:bg-indigo-700">Take</button>
              </form>
            <?php elseif($t->it_id===auth()->id() && $t->status==='ON_PROGRESS'): ?>
              <form method="POST" class="inline" action="<?php echo e(route('it.ticket.release',$t->id)); ?>"><?php echo csrf_field(); ?>
                <button class="rounded-lg bg-brand-700 px-3 py-1.5 text-tulisan-50 hover:bg-gray-300">Lepas</button>
              </form>
              <form method="POST" class="inline" action="<?php echo e(route('it.ticket.close',$t->id)); ?>"><?php echo csrf_field(); ?>
                <button class="rounded-lg bg-emerald-600 px-3 py-1.5 text-white hover:bg-emerald-700">Tutup</button>
              </form>
            <?php else: ?>
              <span class="text-xs text-gray-500">Sudah diambil</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </tbody>
    </table>
  </div>
   
  <div class="block md:hidden space-y-3">
    <?php $__empty_1 = true; $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between gap-3">
          <div>
            <a href="<?php echo e(route('ticket.show',$t->id)); ?>" class="font-semibold text-indigo-600 hover:underline">
              <?php echo e($t->nomor_tiket); ?>

            </a>
            <div class="mt-1 text-xs text-gray-500">
              Dibuat: <?php echo e($t->created_at->format('d M Y H:i')); ?>

            </div>
          </div>

          <?php
            $badge = match($t->status){
              'OPEN'             => 'bg-gray-100 text-gray-700 ring-gray-200',
              'ON_PROGRESS'      => 'bg-amber-100 text-amber-800 ring-amber-200',
              'ESKALASI_VENDOR'  => 'bg-fuchsia-100 text-fuchsia-800 ring-fuchsia-200',
              'VENDOR_RESOLVED'  => 'bg-indigo-100 text-indigo-800 ring-indigo-200',
              'CLOSED'           => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
              default            => 'bg-gray-100 text-gray-700 ring-gray-200',
            };
          ?>
          <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium ring-1 <?php echo e($badge); ?>"><?php echo e($t->status); ?></span>
        </div>

        <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
          <div class="text-gray-500">Kategori</div><div class="font-medium truncate"><?php echo e($t->kategori); ?></div>
          <div class="text-gray-500">Pembuat</div><div class="font-medium truncate"><?php echo e($t->user->name ?? '-'); ?></div>
          <div class="text-gray-500">Handler</div><div class="font-medium truncate"><?php echo e($t->it->name ?? '-'); ?></div>
        </div>

        <div class="mt-3 flex flex-wrap gap-2">
          <a href="<?php echo e(route('ticket.show',$t->id)); ?>" class="rounded-lg bg-gradient-to-r from-blue-500 to-sky-500 px-3 py-2 text-tulisan-50 hover:bg-gray-800">Detail</a>

          <?php if($t->status==='OPEN' || ($t->status!=='CLOSED' && !$t->it_id)): ?>
            <form method="POST" action="<?php echo e(route('it.ticket.take',$t->id)); ?>"><?php echo csrf_field(); ?>
              <button class="rounded-lg bg-indigo-600 px-3 py-2 text-white hover:bg-indigo-700 w-full sm:w-auto">Ambil Alih</button>
            </form>
          <?php elseif($t->it_id===auth()->id() && $t->status==='ON_PROGRESS'): ?>
            <form method="POST" action="<?php echo e(route('it.ticket.release',$t->id)); ?>"><?php echo csrf_field(); ?>
              <button class="rounded-lg bg-gray-200 px-3 py-2 text-gray-800 hover:bg-gray-300 w-full sm:w-auto">Lepas</button>
            </form>
            <form method="POST" action="<?php echo e(route('it.ticket.close',$t->id)); ?>"><?php echo csrf_field(); ?>
              <button class="rounded-lg bg-emerald-600 px-3 py-2 text-white hover:bg-emerald-700 w-full sm:w-auto">Tutup</button>
            </form>
          <?php else: ?>
            <span class="text-xs text-gray-500 self-center">Sudah diambil</span>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
      <div class="text-center text-gray-500 py-8">Tidak ada tiket.</div>
    <?php endif; ?>
  </div>

<div class="mt-4">
  <div class="grid grid-cols-1 md:grid-cols-3 items-center gap-3">
    
    <div class="text-sm text-gray-500 text-center md:text-left min-w-0">
      Tiket <?php echo e($tickets->firstItem()); ?> sampai <?php echo e($tickets->lastItem()); ?> dari total <?php echo e($tickets->total()); ?> Tiket
    </div>

    
    <div class="flex justify-center">
      <?php echo $tickets->appends(request()->except('page'))->links('pagination::tailwind'); ?>

    </div>

    
    <div></div>
  </div>
</div>


<script>
// Tidak perlu JS untuk subkategori; pencarian digabung dalam kolom q
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\helpdesk-app\resources\views/it/dashboard.blade.php ENDPATH**/ ?>