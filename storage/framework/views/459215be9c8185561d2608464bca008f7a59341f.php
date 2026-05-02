<?php $__env->startSection('title','Parameter'); ?>

<?php $__env->startSection('content'); ?>
<div class="w-full max-w-none p-3 sm:p-5 text-xs sm:text-sm space-y-6">
  <div>
    <h1 class="text-xl sm:text-2xl font-semibold text-gray-900 tracking-tight">Parameter</h1>
    <p class="mt-1 text-sm text-gray-500">Kelola kategori, subkategori, root cause, detail root cause (penutupan), vendor, dan visibilitas IT pada penugasan.</p>
  </div>

  <?php if(session('success')): ?>
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm" role="status">
      <?php echo e(session('success')); ?>

    </div>
  <?php endif; ?>
  <?php if(session('error')): ?>
    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-sm" role="alert">
      <?php echo e(session('error')); ?>

    </div>
  <?php endif; ?>

  <?php
    $card = 'rounded-2xl bg-white shadow-md ring-1 ring-gray-100 min-w-0';
    $cardHead = 'flex flex-col gap-4 border-b border-gray-100 bg-gradient-to-r from-gray-50/80 to-white px-4 py-4 sm:px-5 sm:flex-row sm:items-center sm:justify-between';
    $cardTitle = 'text-base font-semibold text-gray-900';
    $input = 'w-full min-w-0 rounded-lg border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500';
    $btnPrimary = 'inline-flex shrink-0 items-center justify-center rounded-lg bg-gradient-to-r from-blue-500 to-sky-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:from-blue-600 hover:to-sky-600';
    $btnSecondary = 'inline-flex shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50';
    $tableWrap = 'max-h-56 overflow-x-auto overflow-y-auto sm:max-h-64';
    $th = 'px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500';
    $td = 'px-4 py-2.5 text-sm text-gray-800';
  ?>

  <div class="grid min-w-0 grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
    
    <section class="<?php echo e($card); ?>">
      <div class="<?php echo e($cardHead); ?>">
        <div>
          <h2 class="<?php echo e($cardTitle); ?>">Kategori</h2>
          <p class="mt-0.5 text-xs text-gray-500">Master kategori tiket.</p>
        </div>
        <form method="POST" action="<?php echo e(route('it.parameters.category.store')); ?>" class="flex w-full flex-col gap-2 sm:w-auto sm:max-w-md sm:flex-row sm:items-center">
          <?php echo csrf_field(); ?>
          <input name="name" required class="<?php echo e($input); ?> sm:flex-1" placeholder="Nama kategori" />
          <button type="submit" class="<?php echo e($btnPrimary); ?>">Tambah</button>
        </form>
      </div>
      <div class="<?php echo e($tableWrap); ?>">
        <table class="min-w-full divide-y divide-gray-100 text-sm">
          <thead class="sticky top-0 z-10 bg-gray-50/95 backdrop-blur-sm">
            <tr>
              <th class="<?php echo e($th); ?> w-10">#</th>
              <th class="<?php echo e($th); ?>">Nama</th>
              <th class="<?php echo e($th); ?> w-24 text-right sm:text-left">Sub</th>
              <th class="<?php echo e($th); ?> w-24">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 bg-white">
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <tr class="transition-colors hover:bg-gray-50/80">
                <td class="<?php echo e($td); ?> text-gray-500"><?php echo e($i + 1); ?></td>
                <td class="<?php echo e($td); ?> font-medium text-gray-900"><?php echo e($c->name); ?></td>
                <td class="<?php echo e($td); ?> text-right tabular-nums text-gray-600 sm:text-left"><?php echo e($c->subcategories->count()); ?></td>
                <td class="<?php echo e($td); ?>">
                  <form method="POST" action="<?php echo e(route('it.parameters.category.delete', $c->id)); ?>" onsubmit="return confirm('Hapus kategori?');" class="inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-700 hover:underline">Hapus</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </tbody>
        </table>
      </div>
    </section>

    
    <section class="<?php echo e($card); ?>">
      <div class="space-y-4 border-b border-gray-100 bg-gradient-to-r from-gray-50/80 to-white px-4 py-4 sm:px-5">
        <div>
          <h2 class="<?php echo e($cardTitle); ?>">Subkategori</h2>
          <p class="mt-0.5 text-xs text-gray-500">Tautkan ke kategori induk.</p>
        </div>
        <form method="POST" action="<?php echo e(route('it.parameters.subcategory.store')); ?>" class="flex w-full min-w-0 flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-stretch sm:gap-x-2 sm:gap-y-2">
          <?php echo csrf_field(); ?>
          <select name="category_id" required class="<?php echo e($input); ?> w-full shrink-0 sm:w-44">
            <option value="">Kategori</option>
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
          <input name="name" required class="<?php echo e($input); ?> w-full min-w-0 sm:min-w-[10rem] sm:flex-1" placeholder="Nama subkategori" />
          <button type="submit" class="<?php echo e($btnPrimary); ?> w-full shrink-0 sm:w-auto sm:self-center">Tambah</button>
        </form>
      </div>
      <div class="<?php echo e($tableWrap); ?>">
        <table class="min-w-full divide-y divide-gray-100 text-sm">
          <thead class="sticky top-0 z-10 bg-gray-50/95 backdrop-blur-sm">
            <tr>
              <th class="<?php echo e($th); ?> w-10">#</th>
              <th class="<?php echo e($th); ?>">Subkategori</th>
              <th class="<?php echo e($th); ?>">Kategori</th>
              <th class="<?php echo e($th); ?> w-24">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 bg-white">
            <?php $count = 0; ?>
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <?php $__currentLoopData = $c->subcategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $count++; ?>
                <tr class="transition-colors hover:bg-gray-50/80">
                  <td class="<?php echo e($td); ?> text-gray-500"><?php echo e($count); ?></td>
                  <td class="<?php echo e($td); ?>"><?php echo e($s->name); ?></td>
                  <td class="<?php echo e($td); ?> text-gray-600"><?php echo e($c->name); ?></td>
                  <td class="<?php echo e($td); ?>">
                    <form method="POST" action="<?php echo e(route('it.parameters.subcategory.delete', $s->id)); ?>" onsubmit="return confirm('Hapus subkategori?');" class="inline">
                      <?php echo csrf_field(); ?>
                      <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-700 hover:underline">Hapus</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if($count === 0): ?>
              <tr>
                <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada subkategori</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

    
    <section class="<?php echo e($card); ?>">
      <div class="<?php echo e($cardHead); ?>">
        <div>
          <h2 class="<?php echo e($cardTitle); ?>">Root cause</h2>
          <p class="mt-0.5 text-xs text-gray-500">Master analisis / penutupan. Daftar detail per root cause ada di card <span class="font-medium text-gray-700">Detail root cause</span>.</p>
        </div>
        <form method="POST" action="<?php echo e(route('it.parameters.rootcause.store')); ?>" class="flex w-full flex-col gap-2 sm:w-auto sm:max-w-md sm:flex-row sm:items-center">
          <?php echo csrf_field(); ?>
          <input name="name" required class="<?php echo e($input); ?> sm:flex-1" placeholder="Nama root cause" />
          <button type="submit" class="<?php echo e($btnPrimary); ?>">Tambah</button>
        </form>
      </div>
      <div class="<?php echo e($tableWrap); ?>">
        <table class="min-w-full divide-y divide-gray-100 text-sm">
          <thead class="sticky top-0 z-10 bg-gray-50/95 backdrop-blur-sm">
            <tr>
              <th class="<?php echo e($th); ?> w-10">#</th>
              <th class="<?php echo e($th); ?>">Nama</th>
              <th class="<?php echo e($th); ?> w-16 text-center tabular-nums" title="Jumlah detail">Det.</th>
              <th class="<?php echo e($th); ?> w-24">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 bg-white">
            <?php $__currentLoopData = $rootCauses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $rc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <tr class="transition-colors hover:bg-gray-50/80">
                <td class="<?php echo e($td); ?> text-gray-500"><?php echo e($i + 1); ?></td>
                <td class="<?php echo e($td); ?> font-medium text-gray-900"><?php echo e($rc->name); ?></td>
                <td class="<?php echo e($td); ?> text-center text-gray-600 tabular-nums"><?php echo e($rc->details->count()); ?></td>
                <td class="<?php echo e($td); ?>">
                  <form method="POST" action="<?php echo e(route('it.parameters.rootcause.delete', $rc->id)); ?>" onsubmit="return confirm('Hapus root cause beserta semua detailnya?');" class="inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-700 hover:underline">Hapus</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if(count($rootCauses) === 0): ?>
              <tr>
                <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada root cause</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

    
    <div class="min-w-0 md:col-span-2 lg:col-span-3 grid grid-cols-1 gap-6 md:grid-cols-3">
    
    <section class="<?php echo e($card); ?>">
      <div class="space-y-4 border-b border-gray-100 bg-gradient-to-r from-gray-50/80 to-white px-4 py-4 sm:px-5">
        <div>
          <h2 class="<?php echo e($cardTitle); ?>">Detail root cause</h2>
          <p class="mt-0.5 text-xs text-gray-500">Tautkan ke root cause induk. Dipakai sebagai radio saat IT menutup tiket; <span class="font-medium text-gray-800">closed note</span> mengikuti form tutup tiket.</p>
        </div>
        <form method="POST" action="<?php echo e(route('it.parameters.rootcause.detail.store')); ?>" class="flex w-full min-w-0 flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-stretch sm:gap-x-2 sm:gap-y-2">
          <?php echo csrf_field(); ?>
          <select name="root_cause_id" required class="<?php echo e($input); ?> w-full shrink-0 sm:w-44" <?php if(count($rootCauses) === 0): ?> disabled <?php endif; ?>>
            <option value="">Root cause</option>
            <?php $__currentLoopData = $rootCauses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($rc->id); ?>"><?php echo e($rc->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
          <input name="label" required maxlength="191" class="<?php echo e($input); ?> w-full min-w-0 sm:min-w-[10rem] sm:flex-1" placeholder="Nama detail (label radio)" <?php if(count($rootCauses) === 0): ?> disabled <?php endif; ?> />
          <label class="inline-flex w-full min-w-0 cursor-pointer items-center gap-2 rounded-lg border border-transparent px-0 py-1 text-xs text-gray-700 sm:w-auto sm:shrink-0 sm:self-center sm:border-0 sm:px-1">
            <input type="checkbox" name="is_other" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" <?php if(count($rootCauses) === 0): ?> disabled <?php endif; ?> />
            <span class="leading-snug">Lainnya → closed note wajib</span>
          </label>
          <button type="submit" class="<?php echo e($btnPrimary); ?> w-full shrink-0 sm:w-auto sm:self-center" <?php if(count($rootCauses) === 0): ?> disabled <?php endif; ?>>Tambah</button>
        </form>
      </div>
      <div class="<?php echo e($tableWrap); ?>">
        <table class="min-w-full divide-y divide-gray-100 text-sm">
          <thead class="sticky top-0 z-10 bg-gray-50/95 backdrop-blur-sm">
            <tr>
              <th class="<?php echo e($th); ?> w-10">#</th>
              <th class="<?php echo e($th); ?>">Detail</th>
              <th class="<?php echo e($th); ?>">Root cause</th>
              <th class="<?php echo e($th); ?> w-24">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 bg-white">
            <?php $detailCount = 0; ?>
            <?php $__currentLoopData = $rootCauses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <?php $__currentLoopData = $rc->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $detailCount++; ?>
                <tr class="transition-colors hover:bg-gray-50/80">
                  <td class="<?php echo e($td); ?> text-gray-500"><?php echo e($detailCount); ?></td>
                  <td class="<?php echo e($td); ?>">
                    <span class="text-gray-800"><?php echo e($d->label); ?></span>
                    <?php if($d->is_other): ?>
                      <span class="ml-1.5 align-middle rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-semibold text-amber-900">Lainnya</span>
                    <?php endif; ?>
                  </td>
                  <td class="<?php echo e($td); ?> text-gray-600"><?php echo e($rc->name); ?></td>
                  <td class="<?php echo e($td); ?>">
                    <form method="POST" action="<?php echo e(route('it.parameters.rootcause.detail.delete', $d)); ?>" onsubmit="return confirm('Hapus detail ini?');" class="inline">
                      <?php echo csrf_field(); ?>
                      <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-700 hover:underline">Hapus</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if(count($rootCauses) === 0): ?>
              <tr>
                <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">Tambah root cause terlebih dahulu.</td>
              </tr>
            <?php elseif($detailCount === 0): ?>
              <tr>
                <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada detail — penutupan untuk root cause terkait hanya memakai closed note bebas.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

    
    <section class="<?php echo e($card); ?>">
      <div class="<?php echo e($cardHead); ?>">
        <div>
          <h2 class="<?php echo e($cardTitle); ?>">Vendor</h2>
          <p class="mt-0.5 text-xs text-gray-500">Daftar akun vendor.</p>
        </div>
        <a href="<?php echo e(route('it.users.index')); ?>" class="<?php echo e($btnPrimary); ?>">Kelola vendor</a>
      </div>
      <div class="<?php echo e($tableWrap); ?>">
        <table class="min-w-full divide-y divide-gray-100 text-sm">
          <thead class="sticky top-0 z-10 bg-gray-50/95 backdrop-blur-sm">
            <tr>
              <th class="<?php echo e($th); ?> w-10">#</th>
              <th class="<?php echo e($th); ?>">Nama</th>
              <th class="<?php echo e($th); ?> min-w-[10rem]">Email</th>
              <th class="<?php echo e($th); ?> w-20">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 bg-white">
            <?php $__currentLoopData = $vendors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <tr class="transition-colors hover:bg-gray-50/80">
                <td class="<?php echo e($td); ?> text-gray-500"><?php echo e($i + 1); ?></td>
                <td class="<?php echo e($td); ?> font-medium text-gray-900"><?php echo e($v->name); ?></td>
                <td class="<?php echo e($td); ?> break-all text-gray-600"><?php echo e($v->email); ?></td>
                <td class="<?php echo e($td); ?>">
                  <a href="<?php echo e(route('it.users.edit', $v->id)); ?>" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 hover:underline">Edit</a>
                </td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if(count($vendors) === 0): ?>
              <tr>
                <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada vendor</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

    
    <section class="<?php echo e($card); ?>">
      <form method="POST" action="<?php echo e(route('it.parameters.it.visibility')); ?>">
        <?php echo csrf_field(); ?>
        <div class="<?php echo e($cardHead); ?>">
          <div>
            <h2 class="<?php echo e($cardTitle); ?>">Ditugaskan ke (IT)</h2>
            <p class="mt-0.5 text-xs text-gray-500">Pilih user IT yang muncul di dropdown penugasan.</p>
          </div>
          <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center">
            <a href="<?php echo e(route('it.users.index')); ?>" class="<?php echo e($btnSecondary); ?> text-center">Kelola IT</a>
            <button type="submit" class="<?php echo e($btnPrimary); ?>">Simpan</button>
          </div>
        </div>
        <div class="<?php echo e($tableWrap); ?> max-h-72 sm:max-h-80">
          <table class="min-w-full divide-y divide-gray-100 text-sm">
            <thead class="sticky top-0 z-10 bg-gray-50/95 backdrop-blur-sm">
              <tr>
                <th class="<?php echo e($th); ?> w-10">#</th>
                <th class="<?php echo e($th); ?>">Nama</th>
                <th class="<?php echo e($th); ?> min-w-[10rem]">Email</th>
                <th class="<?php echo e($th); ?> w-28">Tampilkan</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
              <?php $__currentLoopData = $its; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="transition-colors hover:bg-gray-50/80">
                  <td class="<?php echo e($td); ?> text-gray-500"><?php echo e($i + 1); ?></td>
                  <td class="<?php echo e($td); ?> font-medium text-gray-900"><?php echo e($it->name); ?></td>
                  <td class="<?php echo e($td); ?> break-all text-gray-600"><?php echo e($it->email); ?></td>
                  <td class="<?php echo e($td); ?>">
                    <input type="checkbox" name="visible[]" value="<?php echo e($it->id); ?>" <?php if($it->visible_on_assign): echo 'checked'; endif; ?>
                      class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                  </td>
                </tr>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              <?php if(count($its) === 0): ?>
                <tr>
                  <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada user IT</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </form>
    </section>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\helpdesk-app\resources\views/it/parameters.blade.php ENDPATH**/ ?>