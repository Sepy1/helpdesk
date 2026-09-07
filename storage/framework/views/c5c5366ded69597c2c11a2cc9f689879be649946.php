<article class="<?php echo e($card); ?>" id="ticket-classification-settings">
  <div class="<?php echo e($cardHead); ?>">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
      <div>
        <h2 class="<?php echo e($cardTitle); ?>">Pengaturan Klasifikasi Tiket</h2>
        <p class="<?php echo e($cardHint); ?>">Struktur kategori, subkategori, dan jenis permintaan dalam tampilan tree.</p>
      </div>
      <form method="POST" action="<?php echo e(route('it.parameters.category.store')); ?>" class="flex w-full flex-col gap-2 sm:flex-row lg:max-w-md">
        <?php echo csrf_field(); ?>
        <input name="name" required class="<?php echo e($input); ?>" placeholder="Nama kategori baru">
        <button type="submit" class="<?php echo e($btnPrimary); ?>">Tambah Kategori</button>
      </form>
    </div>
  </div>

  <div class="max-h-[30rem] overflow-auto p-4 sm:p-5">
    <div class="min-w-[42rem] rounded-lg border border-slate-200 bg-slate-50/60 p-3 sm:p-4">
      <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <details class="group/tree mb-2 last:mb-0">
          <summary class="flex cursor-pointer list-none items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2.5 shadow-sm hover:border-blue-200 hover:bg-blue-50/40 [&::-webkit-details-marker]:hidden">
            <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform group-open/tree:rotate-90" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L10.94 10 7.23 6.29a.75.75 0 1 1 1.06-1.06l4.24 4.24a.75.75 0 0 1 0 1.06l-4.24 4.24a.75.75 0 0 1-1.08 0Z" clip-rule="evenodd"/></svg>
            <svg class="h-5 w-5 shrink-0 text-amber-500" viewBox="0 0 24 24" fill="currentColor"><path d="M3 6a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a3 3 0 0 1-3 3H5a2 2 0 0 1-2-2V6Z"/></svg>
            <span class="min-w-0 flex-1 truncate text-sm font-semibold text-slate-900"><?php echo e($category->name); ?></span>
            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-500"><?php echo e($category->subcategories->count()); ?> sub</span>
            <div class="flex shrink-0 items-center gap-2" onclick="event.stopPropagation()">
              <form method="POST" action="<?php echo e(route('it.parameters.category.status', $category)); ?>" class="inline-flex items-center gap-1.5"><?php echo csrf_field(); ?><input type="hidden" name="is_enabled" value="0"><input type="checkbox" name="is_enabled" value="1" <?php if($category->is_enabled): echo 'checked'; endif; ?> onchange="this.form.submit()" aria-label="Enable kategori <?php echo e($category->name); ?>" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"><span class="text-xs text-slate-500">Enable</span></form>
              <form method="POST" action="<?php echo e(route('it.parameters.category.delete', $category->id)); ?>" onsubmit="return confirm('Hapus kategori beserta seluruh data turunannya?');"><?php echo csrf_field(); ?><button type="submit" class="<?php echo e($btnDanger); ?>">Hapus</button></form>
            </div>
          </summary>

          <div class="relative ml-5 border-l-2 border-slate-200 pb-1 pl-6 pt-2">
            <?php $__currentLoopData = $category->subcategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subcategory): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <details class="group/sub relative mb-2 last:mb-0">
                <summary class="flex cursor-pointer list-none items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 hover:border-blue-200 hover:bg-blue-50/40 [&::-webkit-details-marker]:hidden before:absolute before:-left-6 before:top-5 before:h-px before:w-6 before:bg-slate-200">
                  <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform group-open/sub:rotate-90" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L10.94 10 7.23 6.29a.75.75 0 1 1 1.06-1.06l4.24 4.24a.75.75 0 0 1 0 1.06l-4.24 4.24a.75.75 0 0 1-1.08 0Z" clip-rule="evenodd"/></svg>
                  <svg class="h-5 w-5 shrink-0 text-amber-400" viewBox="0 0 24 24" fill="currentColor"><path d="M3 6a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a3 3 0 0 1-3 3H5a2 2 0 0 1-2-2V6Z"/></svg>
                  <span class="min-w-0 flex-1 truncate text-sm font-medium text-slate-800"><?php echo e($subcategory->name); ?></span>
                  <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-500"><?php echo e($subcategory->requestTypes->count()); ?> jenis</span>
                  <div class="flex shrink-0 items-center gap-2" onclick="event.stopPropagation()">
                    <form method="POST" action="<?php echo e(route('it.parameters.subcategory.status', $subcategory)); ?>" class="inline-flex items-center gap-1.5"><?php echo csrf_field(); ?><input type="hidden" name="is_enabled" value="0"><input type="checkbox" name="is_enabled" value="1" <?php if($subcategory->is_enabled): echo 'checked'; endif; ?> onchange="this.form.submit()" aria-label="Enable subkategori <?php echo e($subcategory->name); ?>" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"><span class="text-xs text-slate-500">Enable</span></form>
                    <form method="POST" action="<?php echo e(route('it.parameters.subcategory.delete', $subcategory->id)); ?>" onsubmit="return confirm('Hapus subkategori beserta seluruh jenis permintaannya?');"><?php echo csrf_field(); ?><button type="submit" class="<?php echo e($btnDanger); ?>">Hapus</button></form>
                  </div>
                </summary>

                <div class="relative ml-5 border-l-2 border-slate-200 pb-1 pl-6 pt-2">
                  <?php $__currentLoopData = $subcategory->requestTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $requestType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="relative mb-2 flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 last:mb-0 before:absolute before:-left-6 before:top-1/2 before:h-px before:w-6 before:bg-slate-200">
                      <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded border border-blue-200 bg-blue-50 text-blue-600">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-8 8a1 1 0 0 1-1.414 0l-4-4A1 1 0 0 1 4.71 9.29L8 12.586l7.296-7.296a1 1 0 0 1 1.408 0Z" clip-rule="evenodd"/></svg>
                      </span>
                      <span class="min-w-0 flex-1 truncate text-sm text-slate-700"><?php echo e($requestType->name); ?></span>
                      <form method="POST" action="<?php echo e(route('it.parameters.request_type.status', $requestType)); ?>" class="inline-flex items-center gap-1.5"><?php echo csrf_field(); ?><input type="hidden" name="is_enabled" value="0"><input type="checkbox" name="is_enabled" value="1" <?php if($requestType->is_enabled): echo 'checked'; endif; ?> onchange="this.form.submit()" aria-label="Enable jenis permintaan <?php echo e($requestType->name); ?>" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"><span class="text-xs text-slate-500">Enable</span></form>
                      <form method="POST" action="<?php echo e(route('it.parameters.request_type.delete', $requestType)); ?>" onsubmit="return confirm('Hapus jenis permintaan?');"><?php echo csrf_field(); ?><button type="submit" class="<?php echo e($btnDanger); ?>">Hapus</button></form>
                    </div>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                  <form method="POST" action="<?php echo e(route('it.parameters.request_type.store')); ?>" class="relative mt-2 flex gap-2 rounded-lg border border-dashed border-slate-300 bg-white/70 p-2 before:absolute before:-left-6 before:top-1/2 before:h-px before:w-6 before:bg-slate-200">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="subcategory_id" value="<?php echo e($subcategory->id); ?>">
                    <input name="name" required class="<?php echo e($input); ?>" placeholder="Nama jenis permintaan baru">
                    <button type="submit" class="<?php echo e($btnPrimary); ?>">Tambah Jenis</button>
                  </form>
                </div>
              </details>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <form method="POST" action="<?php echo e(route('it.parameters.subcategory.store')); ?>" class="relative mt-2 flex gap-2 rounded-lg border border-dashed border-slate-300 bg-white/70 p-2 before:absolute before:-left-6 before:top-1/2 before:h-px before:w-6 before:bg-slate-200">
              <?php echo csrf_field(); ?>
              <input type="hidden" name="category_id" value="<?php echo e($category->id); ?>">
              <input name="name" required class="<?php echo e($input); ?>" placeholder="Nama subkategori baru">
              <button type="submit" class="<?php echo e($btnPrimary); ?>">Tambah Subkategori</button>
            </form>
          </div>
        </details>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="py-10 text-center">
          <svg class="mx-auto h-10 w-10 text-slate-300" viewBox="0 0 24 24" fill="currentColor"><path d="M3 6a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a3 3 0 0 1-3 3H5a2 2 0 0 1-2-2V6Z"/></svg>
          <p class="mt-2 text-sm text-slate-500">Belum ada kategori. Tambahkan kategori pertama melalui form di atas.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</article>
<?php /**PATH C:\laragon\www\helpdesk-app\resources\views/it/partials/ticket-classification-settings.blade.php ENDPATH**/ ?>