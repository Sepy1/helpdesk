
<?php $__env->startSection('title','Tiket Saya'); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
  
  <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between mb-4">
    <div>
      <h2 class="text-lg font-semibold text-gray-800">Tiket Saya</h2>
      <p class="text-sm text-gray-500">Klik nomor tiket untuk melihat detail.</p>
    </div>

    <form method="GET" action="<?php echo e(route('cabang.tickets')); ?>" class="grid grid-cols-2 sm:flex gap-2">
      <input
        type="text"
        name="q"
        value="<?php echo e(request('q')); ?>"
        placeholder="Cari nomor / deskripsi"
        class="col-span-2 sm:col-span-1 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
      >

      <select name="kategori" class="rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
        <option value="">Kategori</option>
        <?php $__currentLoopData = ($kategori ?? ['JARINGAN','LAYANAN','CBS','OTHER']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <option value="<?php echo e($k); ?>" <?php if(request('kategori')===$k): echo 'selected'; endif; ?>><?php echo e($k); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </select>

      <select name="status" class="rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
        <option value="">Status</option>
        <?php $__currentLoopData = ($status ?? ['OPEN','ON_PROGRESS','ESKALASI_VENDOR','VENDOR_RESOLVED','CLOSED']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <option value="<?php echo e($s); ?>" <?php if(request('status')===$s): echo 'selected'; endif; ?>><?php echo e($s); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </select>

      <button class="rounded-lg bg-gradient-to-r from-blue-500 to-sky-500 text-white px-3 py-2">Filter</button>

      <?php if(request()->hasAny(['q','kategori','status'])): ?>
        <a href="<?php echo e(route('cabang.tickets')); ?>" class="text-sm px-2 py-2 text-gray-600 hover:underline">Reset</a>
      <?php endif; ?>
    </form>
  </div>

  
  <div class="hidden md:block overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-gray-50 text-gray-600">
        <tr>
          <th class="py-3 px-4 text-left">#</th>
          <th class="py-3 px-4 text-left">Nomor</th>
          <th class="py-3 px-4 text-left">Kategori</th>
          <th class="py-3 px-4 text-left">Status</th>
          <th class="py-3 px-4 text-left">Dibuat</th>
          <th class="py-3 px-4 text-left">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php $__empty_1 = true; $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <tr class="hover:bg-gray-50">
            <td class="py-3 px-4 text-gray-500"><?php echo e($tickets->firstItem() + $i); ?></td>
            <td class="py-3 px-4 font-medium">
              <a href="<?php echo e(route('ticket.show', $t->id)); ?>" class="text-indigo-600 hover:underline">
                <?php echo e($t->nomor_tiket); ?>

              </a>
            </td>
            <td class="py-3 px-4"><?php echo e($t->kategori); ?></td>
            <td class="py-3 px-4">
              <?php
                $badge = match($t->status){
                  'OPEN'             => 'bg-red-100 text-gray-700 ring-gray-200',
                  'ON_PROGRESS'      => 'bg-amber-100 text-amber-800 ring-amber-200',
                  'ESKALASI_VENDOR'  => 'bg-fuchsia-100 text-fuchsia-800 ring-fuchsia-200',
                  'VENDOR_RESOLVED'  => 'bg-indigo-100 text-indigo-800 ring-indigo-200',
                  'CLOSED'           => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
                  default            => 'bg-gray-100 text-gray-700 ring-gray-200',
                };
              ?>
              <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 <?php echo e($badge); ?>">
                <?php echo e($t->status); ?>

              </span>
            </td>
            <td class="py-3 px-4 whitespace-nowrap"><?php echo e($t->created_at->format('d M Y H:i')); ?></td>
            <td class="py-3 px-4">
              <a href="<?php echo e(route('ticket.show', $t->id)); ?>"
                 class="inline-flex items-center rounded-lg bg-gradient-to-r from-blue-500 to-sky-500 px-3 py-1.5 text-white hover:bg-gray-800">
                Detail
              </a>
            </td>
          </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
          <tr><td colspan="6" class="py-6 px-4 text-center text-gray-500">Tidak ada tiket.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  
  <div class="md:hidden space-y-3">
    <?php $__empty_1 = true; $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <?php
        $badge = match($t->status){
          'OPEN'             => 'bg-red-100 text-gray-700 ring-gray-200',
          'ON_PROGRESS'      => 'bg-amber-100 text-amber-800 ring-amber-200',
          'ESKALASI_VENDOR'  => 'bg-fuchsia-100 text-fuchsia-800 ring-fuchsia-200',
          'VENDOR_RESOLVED'  => 'bg-indigo-100 text-indigo-800 ring-indigo-200',
          'CLOSED'           => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
          default            => 'bg-gray-100 text-gray-700 ring-gray-200',
        };
      ?>
      <div class="rounded-xl border border-gray-100 p-4">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <a href="<?php echo e(route('ticket.show', $t->id)); ?>" class="block text-indigo-600 font-semibold truncate">
              <?php echo e($t->nomor_tiket); ?>

            </a>
            <div class="text-xs text-gray-500 mt-0.5">
              Dibuat: <?php echo e($t->created_at->format('d M Y H:i')); ?>

            </div>
          </div>
          <span class="shrink-0 inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 <?php echo e($badge); ?>">
            <?php echo e($t->status); ?>

          </span>
        </div>

        <div class="mt-3 flex items-center justify-between text-sm">
          <div class="text-gray-700">
            <span class="text-gray-500">Kategori:</span> <?php echo e($t->kategori); ?>

          </div>
          <a href="<?php echo e(route('ticket.show', $t->id)); ?>"
             class="inline-flex items-center rounded-lg bg-gradient-to-r from-blue-500 to-sky-500 px-3 py-1.5 text-white hover:bg-gray-800">
            Detail
          </a>
        </div>
      </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
      <div class="rounded-xl border border-dashed border-gray-200 p-6 text-center text-gray-500">
        Tidak ada tiket.
      </div>
    <?php endif; ?>
  </div>

  
  <div class="mt-4"><?php echo e($tickets->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\helpdesk-app\resources\views/cabang/my_tickets.blade.php ENDPATH**/ ?>