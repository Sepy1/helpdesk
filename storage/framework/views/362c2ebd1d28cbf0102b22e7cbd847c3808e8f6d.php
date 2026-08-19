<?php $__env->startSection('title','Manajemen Popup'); ?>

<?php $__env->startSection('content'); ?>
<?php
  $input = 'mt-1 h-10 w-full rounded-lg border-slate-200 bg-white text-sm text-slate-800 shadow-sm focus:border-violet-500 focus:ring-violet-500';
  $label = 'text-xs font-semibold uppercase tracking-wide text-slate-500';
?>

<div
  class="w-full max-w-none pb-8"
  x-data="{ previewOpen: false, preview: { title: '', image: '', description: '' } }"
>
  <?php if(session('success')): ?>
    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800"><?php echo e(session('success')); ?></div>
  <?php endif; ?>
  <?php if($errors->any()): ?>
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-700">
      <ul class="list-disc pl-5 space-y-1">
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <li><?php echo e($error); ?></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="mb-4">
    <div class="flex flex-wrap items-center gap-2 text-xs font-medium text-slate-500">
      <span class="rounded-md border border-blue-100 bg-blue-50 px-2 py-1 text-blue-700">IT Popup</span>
      <span><?php echo e(now()->format('d/m/Y H:i')); ?></span>
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
        <form method="POST" action="<?php echo e(route('it.popups.store')); ?>" enctype="multipart/form-data" class="mt-4 space-y-4">
          <?php echo csrf_field(); ?>
          <div>
            <label class="<?php echo e($label); ?>">Judul</label>
            <input type="text" name="title" class="<?php echo e($input); ?>" value="<?php echo e(old('title')); ?>" required>
          </div>
          <div>
            <label class="<?php echo e($label); ?>">Deskripsi</label>
            <textarea name="description" rows="4" class="mt-1 w-full rounded-lg border-slate-200 bg-white text-sm text-slate-800 shadow-sm focus:border-violet-500 focus:ring-violet-500"><?php echo e(old('description')); ?></textarea>
          </div>
          <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div>
              <label class="<?php echo e($label); ?>">Mulai</label>
              <input type="datetime-local" name="starts_at" class="<?php echo e($input); ?>" value="<?php echo e(old('starts_at')); ?>">
            </div>
            <div>
              <label class="<?php echo e($label); ?>">Berakhir</label>
              <input type="datetime-local" name="ends_at" class="<?php echo e($input); ?>" value="<?php echo e(old('ends_at')); ?>">
            </div>
          </div>
          <div>
            <label class="<?php echo e($label); ?>">Gambar</label>
            <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp" class="mt-1 block w-full text-sm text-slate-700">
          </div>
          <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div>
              <label class="<?php echo e($label); ?>">Urutan</label>
              <input type="number" name="sort_order" value="<?php echo e(old('sort_order', 0)); ?>" min="0" class="<?php echo e($input); ?>">
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
        <span class="text-sm text-slate-500"><?php echo e($popups->count()); ?> data</span>
      </div>

      <div class="p-4 sm:p-5 space-y-4">
        <?php $__empty_1 = true; $__currentLoopData = $popups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $popup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <div class="rounded-2xl border border-slate-200 p-4">
            <div class="flex flex-col gap-4 md:flex-row">
              <?php if($popup->image_path): ?>
                <img src="<?php echo e(asset('storage/' . $popup->image_path)); ?>" class="h-36 w-full rounded-xl object-cover md:w-48" alt="<?php echo e($popup->title); ?>">
              <?php endif; ?>
              <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                  <h3 class="text-base font-semibold text-slate-900"><?php echo e($popup->title); ?></h3>
                  <span class="rounded-full px-2 py-0.5 text-xs font-semibold <?php echo e($popup->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'); ?>">
                    <?php echo e($popup->is_active ? 'Aktif' : 'Nonaktif'); ?>

                  </span>
                  <span class="rounded-full px-2 py-0.5 text-xs font-semibold <?php echo e($popup->desktop_active ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-500'); ?>">
                    <?php echo e($popup->desktop_active ? 'Desktop Aktif' : 'Desktop Nonaktif'); ?>

                  </span>
                  <span class="rounded-full px-2 py-0.5 text-xs font-semibold <?php echo e($popup->mobile_active ? 'bg-violet-100 text-violet-700' : 'bg-slate-100 text-slate-500'); ?>">
                    <?php echo e($popup->mobile_active ? 'Mobile Aktif' : 'Mobile Nonaktif'); ?>

                  </span>
                </div>
                <?php if($popup->description): ?>
                  <p class="mt-2 text-sm text-slate-600 whitespace-pre-line"><?php echo e($popup->description); ?></p>
                <?php endif; ?>
                <p class="mt-2 text-xs text-slate-500">
                  Urutan: <?php echo e($popup->sort_order); ?> |
                  Mulai: <?php echo e(optional($popup->starts_at)->format('d M Y H:i') ?? '-'); ?> |
                  Berakhir: <?php echo e(optional($popup->ends_at)->format('d M Y H:i') ?? '-'); ?>

                </p>

                <div class="mt-4 flex flex-wrap gap-2">
                  <button
                    type="button"
                    class="rounded-lg border border-violet-200 px-3 py-2 text-sm font-medium text-violet-700 hover:bg-violet-50"
                    @click="preview = { title: <?php echo \Illuminate\Support\Js::from($popup->title)->toHtml() ?>, image: <?php echo \Illuminate\Support\Js::from($popup->image_path ? asset('storage/' . $popup->image_path) : '')->toHtml() ?>, description: <?php echo \Illuminate\Support\Js::from($popup->description ?? '')->toHtml() ?> }; previewOpen = true"
                  >
                    Preview
                  </button>

                  <form method="POST" action="<?php echo e(route('it.popups.update', $popup)); ?>" enctype="multipart/form-data" class="flex flex-wrap gap-2">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <input type="hidden" name="title" value="<?php echo e($popup->title); ?>">
                    <input type="hidden" name="description" value="<?php echo e($popup->description); ?>">
                    <input type="hidden" name="sort_order" value="<?php echo e($popup->sort_order); ?>">
                    <input type="hidden" name="starts_at" value="<?php echo e(optional($popup->starts_at)->format('Y-m-d\TH:i')); ?>">
                    <input type="hidden" name="ends_at" value="<?php echo e(optional($popup->ends_at)->format('Y-m-d\TH:i')); ?>">
                    <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm">
                      <input type="checkbox" name="is_active" value="1" <?php echo e($popup->is_active ? 'checked' : ''); ?>>
                      <?php echo e($popup->is_active ? 'Matikan' : 'Aktifkan'); ?>

                    </label>
                    <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm">
                      <input type="checkbox" name="desktop_active" value="1" <?php echo e($popup->desktop_active ? 'checked' : ''); ?>>
                      <?php echo e($popup->desktop_active ? 'Desktop Matikan' : 'Desktop Aktifkan'); ?>

                    </label>
                    <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm">
                      <input type="checkbox" name="mobile_active" value="1" <?php echo e($popup->mobile_active ? 'checked' : ''); ?>>
                      <?php echo e($popup->mobile_active ? 'Mobile Matikan' : 'Mobile Aktifkan'); ?>

                    </label>
                    <button class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-800">Simpan Status</button>
                  </form>

                  <form method="POST" action="<?php echo e(route('it.popups.destroy', $popup)); ?>" onsubmit="return confirm('Hapus popup ini?');">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button class="rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50">Hapus</button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
          <div class="rounded-xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500">
            Belum ada popup.
          </div>
        <?php endif; ?>
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\helpdesk-app\resources\views/it/popups/index.blade.php ENDPATH**/ ?>