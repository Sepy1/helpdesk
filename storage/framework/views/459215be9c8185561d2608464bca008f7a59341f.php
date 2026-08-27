<?php $__env->startSection('title','Parameter'); ?>

<?php $__env->startSection('content'); ?>
<?php
  $categories = $categories ?? collect();
  $rootCauses = $rootCauses ?? collect();
  $vendors = $vendors ?? collect();
  $its = $its ?? collect();
  $usersForAiChat = $usersForAiChat ?? collect();

  $categoryCount = $categories->count();
  $subcategoryCount = $categories->sum(fn ($category) => $category->subcategories->count());
  $rootCauseCount = $rootCauses->count();
  $rootCauseDetailCount = $rootCauses->sum(fn ($rootCause) => $rootCause->details->count());
  $visibleItCount = $its->where('visible_on_assign', true)->count();
  $aiUserCount = $usersForAiChat->where('ai_chat_enabled', true)->count();

  $card = 'rounded-lg border border-slate-200 bg-white shadow-sm';
  $cardHead = 'border-b border-slate-100 px-4 py-3 sm:px-5';
  $cardTitle = 'text-base font-semibold text-slate-900';
  $cardHint = 'mt-1 text-xs leading-5 text-slate-500';
  $input = 'h-10 w-full rounded-lg border-slate-200 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500';
  $btnPrimary = 'inline-flex h-10 shrink-0 items-center justify-center rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:cursor-not-allowed disabled:opacity-60';
  $btnSecondary = 'inline-flex h-10 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500';
  $btnDanger = 'inline-flex items-center rounded-md px-2 py-1 text-sm font-medium text-red-600 hover:bg-red-50 hover:text-red-700';
  $tableWrap = 'max-h-72 overflow-auto';
  $th = 'whitespace-nowrap px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500';
  $td = 'px-4 py-2.5 text-sm text-slate-700';
?>

<div class="w-full max-w-none pb-8">
  <div class="space-y-4">
    <section class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
      <div class="min-w-0">
        <div class="flex flex-wrap items-center gap-2 text-xs font-medium text-slate-500">
          <span class="rounded-md border border-blue-100 bg-blue-50 px-2 py-1 text-blue-700">IT Parameter</span>
          <span><?php echo e(now()->format('d/m/Y H:i')); ?></span>
        </div>
        <h1 class="mt-2 text-2xl font-semibold tracking-normal text-slate-900 sm:text-3xl">Parameter</h1>
        <p class="mt-1 max-w-3xl text-sm text-slate-500">Kelola master tiket, penutupan, vendor, penugasan IT, dan akses AI chat.</p>
      </div>

      <div class="flex flex-wrap gap-2">
        <a href="<?php echo e(route('it.users.index')); ?>" class="<?php echo e($btnSecondary); ?>">Kelola user</a>
        <a href="<?php echo e(route('it.dashboard')); ?>" class="<?php echo e($btnPrimary); ?>">Lihat tiket</a>
      </div>
    </section>

    <?php if(session('success')): ?>
      <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm" role="status">
        <?php echo e(session('success')); ?>

      </div>
    <?php endif; ?>
    <?php if(session('error')): ?>
      <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-sm" role="alert">
        <?php echo e(session('error')); ?>

      </div>
    <?php endif; ?>

    <section class="grid grid-cols-2 gap-3 md:grid-cols-4 xl:grid-cols-6">
      <div class="<?php echo e($card); ?> p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Kategori</p>
        <p class="mt-2 text-2xl font-semibold text-slate-950"><?php echo e($categoryCount); ?></p>
      </div>
      <div class="<?php echo e($card); ?> p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Subkategori</p>
        <p class="mt-2 text-2xl font-semibold text-slate-950"><?php echo e($subcategoryCount); ?></p>
      </div>
      <div class="<?php echo e($card); ?> p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Root cause</p>
        <p class="mt-2 text-2xl font-semibold text-slate-950"><?php echo e($rootCauseCount); ?></p>
      </div>
      <div class="<?php echo e($card); ?> p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Detail root</p>
        <p class="mt-2 text-2xl font-semibold text-slate-950"><?php echo e($rootCauseDetailCount); ?></p>
      </div>
      <div class="<?php echo e($card); ?> p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Vendor</p>
        <p class="mt-2 text-2xl font-semibold text-slate-950"><?php echo e($vendors->count()); ?></p>
      </div>
      <div class="<?php echo e($card); ?> p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">IT tampil</p>
        <p class="mt-2 text-2xl font-semibold text-slate-950"><?php echo e($visibleItCount); ?></p>
      </div>
    </section>

    <section class="grid grid-cols-1 gap-4 xl:grid-cols-3">
      <article class="<?php echo e($card); ?>">
        <div class="<?php echo e($cardHead); ?>">
          <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
            <div>
              <h2 class="<?php echo e($cardTitle); ?>">Kategori</h2>
              <p class="<?php echo e($cardHint); ?>">Master kategori tiket yang dipilih saat tiket dibuat.</p>
            </div>
            <form method="POST" action="<?php echo e(route('it.parameters.category.store')); ?>" class="flex w-full flex-col gap-2 sm:flex-row lg:max-w-md">
              <?php echo csrf_field(); ?>
              <input name="name" required class="<?php echo e($input); ?>" placeholder="Nama kategori">
              <button type="submit" class="<?php echo e($btnPrimary); ?>">Tambah</button>
            </form>
          </div>
        </div>
        <div class="<?php echo e($tableWrap); ?>">
          <table class="min-w-full divide-y divide-slate-100">
            <thead class="sticky top-0 z-10 bg-slate-50">
              <tr>
                <th class="<?php echo e($th); ?> w-12">#</th>
                <th class="<?php echo e($th); ?>">Nama</th>
                <th class="<?php echo e($th); ?> w-20 text-right">Sub</th>
                <th class="<?php echo e($th); ?> w-24 text-center">Enable</th>
                <th class="<?php echo e($th); ?> w-24 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
              <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-slate-50">
                  <td class="<?php echo e($td); ?> text-slate-500"><?php echo e($i + 1); ?></td>
                  <td class="<?php echo e($td); ?> font-medium text-slate-900"><?php echo e($category->name); ?></td>
                  <td class="<?php echo e($td); ?> text-right tabular-nums"><?php echo e($category->subcategories->count()); ?></td>
                  <td class="<?php echo e($td); ?> text-center">
                    <form method="POST" action="<?php echo e(route('it.parameters.category.status', $category)); ?>" class="inline">
                      <?php echo csrf_field(); ?>
                      <input type="hidden" name="is_enabled" value="0">
                      <input type="checkbox" name="is_enabled" value="1" <?php if($category->is_enabled): echo 'checked'; endif; ?> onchange="this.form.submit()" aria-label="Enable kategori <?php echo e($category->name); ?>" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    </form>
                  </td>
                  <td class="<?php echo e($td); ?> text-right">
                    <form method="POST" action="<?php echo e(route('it.parameters.category.delete', $category->id)); ?>" onsubmit="return confirm('Hapus kategori?');" class="inline">
                      <?php echo csrf_field(); ?>
                      <button type="submit" class="<?php echo e($btnDanger); ?>">Hapus</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                  <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada kategori.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </article>

      <article class="<?php echo e($card); ?>">
        <div class="<?php echo e($cardHead); ?>">
          <h2 class="<?php echo e($cardTitle); ?>">Subkategori</h2>
          <p class="<?php echo e($cardHint); ?>">Tautkan subkategori ke kategori induk.</p>
          <form method="POST" action="<?php echo e(route('it.parameters.subcategory.store')); ?>" class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-[12rem_minmax(0,1fr)_auto]">
            <?php echo csrf_field(); ?>
            <select name="category_id" required class="<?php echo e($input); ?>">
              <option value="">Kategori</option>
              <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($category->id); ?>"><?php echo e($category->name); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <input name="name" required class="<?php echo e($input); ?>" placeholder="Nama subkategori">
            <button type="submit" class="<?php echo e($btnPrimary); ?>">Tambah</button>
          </form>
        </div>
        <div class="<?php echo e($tableWrap); ?>">
          <table class="min-w-full divide-y divide-slate-100">
            <thead class="sticky top-0 z-10 bg-slate-50">
              <tr>
                <th class="<?php echo e($th); ?> w-12">#</th>
                <th class="<?php echo e($th); ?>">Subkategori</th>
                <th class="<?php echo e($th); ?>">Kategori</th>
                <th class="<?php echo e($th); ?> w-24 text-center">Enable</th>
                <th class="<?php echo e($th); ?> w-24 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
              <?php $subcategoryIndex = 0; ?>
              <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $__currentLoopData = $category->subcategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subcategory): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <?php $subcategoryIndex++; ?>
                  <tr class="hover:bg-slate-50">
                    <td class="<?php echo e($td); ?> text-slate-500"><?php echo e($subcategoryIndex); ?></td>
                    <td class="<?php echo e($td); ?> font-medium text-slate-900"><?php echo e($subcategory->name); ?></td>
                    <td class="<?php echo e($td); ?>"><?php echo e($category->name); ?></td>
                    <td class="<?php echo e($td); ?> text-center">
                      <form method="POST" action="<?php echo e(route('it.parameters.subcategory.status', $subcategory)); ?>" class="inline">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="is_enabled" value="0">
                        <input type="checkbox" name="is_enabled" value="1" <?php if($subcategory->is_enabled): echo 'checked'; endif; ?> onchange="this.form.submit()" aria-label="Enable subkategori <?php echo e($subcategory->name); ?>" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                      </form>
                    </td>
                    <td class="<?php echo e($td); ?> text-right">
                      <form method="POST" action="<?php echo e(route('it.parameters.subcategory.delete', $subcategory->id)); ?>" onsubmit="return confirm('Hapus subkategori?');" class="inline">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="<?php echo e($btnDanger); ?>">Hapus</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              <?php if($subcategoryIndex === 0): ?>
                <tr>
                  <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada subkategori.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </article>

      <article class="<?php echo e($card); ?>">
        <div class="<?php echo e($cardHead); ?>">
          <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
            <div>
              <h2 class="<?php echo e($cardTitle); ?>">Root cause</h2>
              <p class="<?php echo e($cardHint); ?>">Master penyebab saat tiket ditutup.</p>
            </div>
            <form method="POST" action="<?php echo e(route('it.parameters.rootcause.store')); ?>" class="flex w-full flex-col gap-2 sm:flex-row lg:max-w-md">
              <?php echo csrf_field(); ?>
              <input name="name" required class="<?php echo e($input); ?>" placeholder="Nama root cause">
              <button type="submit" class="<?php echo e($btnPrimary); ?>">Tambah</button>
            </form>
          </div>
        </div>
        <div class="<?php echo e($tableWrap); ?>">
          <table class="min-w-full divide-y divide-slate-100">
            <thead class="sticky top-0 z-10 bg-slate-50">
              <tr>
                <th class="<?php echo e($th); ?> w-12">#</th>
                <th class="<?php echo e($th); ?>">Nama</th>
                <th class="<?php echo e($th); ?> w-20 text-right">Detail</th>
                <th class="<?php echo e($th); ?> w-24 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
              <?php $__empty_1 = true; $__currentLoopData = $rootCauses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $rootCause): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-slate-50">
                  <td class="<?php echo e($td); ?> text-slate-500"><?php echo e($i + 1); ?></td>
                  <td class="<?php echo e($td); ?> font-medium text-slate-900"><?php echo e($rootCause->name); ?></td>
                  <td class="<?php echo e($td); ?> text-right tabular-nums"><?php echo e($rootCause->details->count()); ?></td>
                  <td class="<?php echo e($td); ?> text-right">
                    <form method="POST" action="<?php echo e(route('it.parameters.rootcause.delete', $rootCause->id)); ?>" onsubmit="return confirm('Hapus root cause beserta semua detailnya?');" class="inline">
                      <?php echo csrf_field(); ?>
                      <button type="submit" class="<?php echo e($btnDanger); ?>">Hapus</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                  <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada root cause.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </article>
    </section>

    <section class="grid grid-cols-1 gap-4 xl:grid-cols-3">
      <article class="<?php echo e($card); ?> xl:col-span-2">
        <div class="<?php echo e($cardHead); ?>">
          <h2 class="<?php echo e($cardTitle); ?>">Detail root cause</h2>
          <p class="<?php echo e($cardHint); ?>">Detail akan muncul sebagai pilihan radio saat IT menutup tiket.</p>
          <form method="POST" action="<?php echo e(route('it.parameters.rootcause.detail.store')); ?>" class="mt-3 grid grid-cols-1 gap-2 lg:grid-cols-[12rem_minmax(0,1fr)_auto_auto] lg:items-center">
            <?php echo csrf_field(); ?>
            <select name="root_cause_id" required class="<?php echo e($input); ?>" <?php if($rootCauseCount === 0): echo 'disabled'; endif; ?>>
              <option value="">Root cause</option>
              <?php $__currentLoopData = $rootCauses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rootCause): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($rootCause->id); ?>"><?php echo e($rootCause->name); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <input name="label" required maxlength="191" class="<?php echo e($input); ?>" placeholder="Nama detail root cause" <?php if($rootCauseCount === 0): echo 'disabled'; endif; ?>>
            <label class="inline-flex h-10 items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700">
              <input type="checkbox" name="is_other" value="1" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" <?php if($rootCauseCount === 0): echo 'disabled'; endif; ?>>
              <span>Lainnya</span>
            </label>
            <button type="submit" class="<?php echo e($btnPrimary); ?>" <?php if($rootCauseCount === 0): echo 'disabled'; endif; ?>>Tambah</button>
          </form>
          <p class="mt-2 text-xs text-slate-500">Opsi Lainnya membuat closed note wajib di form close ticket.</p>
        </div>
        <div class="<?php echo e($tableWrap); ?>">
          <table class="min-w-full divide-y divide-slate-100">
            <thead class="sticky top-0 z-10 bg-slate-50">
              <tr>
                <th class="<?php echo e($th); ?> w-12">#</th>
                <th class="<?php echo e($th); ?>">Detail</th>
                <th class="<?php echo e($th); ?>">Root cause</th>
                <th class="<?php echo e($th); ?> w-24 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
              <?php $detailIndex = 0; ?>
              <?php $__currentLoopData = $rootCauses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rootCause): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $__currentLoopData = $rootCause->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <?php $detailIndex++; ?>
                  <tr class="hover:bg-slate-50">
                    <td class="<?php echo e($td); ?> text-slate-500"><?php echo e($detailIndex); ?></td>
                    <td class="<?php echo e($td); ?> font-medium text-slate-900">
                      <?php echo e($detail->label); ?>

                      <?php if($detail->is_other): ?>
                        <span class="ml-2 rounded-md border border-amber-200 bg-amber-50 px-1.5 py-0.5 text-[11px] font-semibold text-amber-700">Lainnya</span>
                      <?php endif; ?>
                    </td>
                    <td class="<?php echo e($td); ?>"><?php echo e($rootCause->name); ?></td>
                    <td class="<?php echo e($td); ?> text-right">
                      <form method="POST" action="<?php echo e(route('it.parameters.rootcause.detail.delete', $detail)); ?>" onsubmit="return confirm('Hapus detail ini?');" class="inline">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="<?php echo e($btnDanger); ?>">Hapus</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              <?php if($rootCauseCount === 0): ?>
                <tr>
                  <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500">Tambah root cause terlebih dahulu.</td>
                </tr>
              <?php elseif($detailIndex === 0): ?>
                <tr>
                  <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada detail root cause.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </article>

      <article class="<?php echo e($card); ?>">
        <div class="<?php echo e($cardHead); ?>">
          <div class="flex items-start justify-between gap-3">
            <div>
              <h2 class="<?php echo e($cardTitle); ?>">Vendor</h2>
              <p class="<?php echo e($cardHint); ?>">Daftar akun vendor untuk eskalasi tiket.</p>
            </div>
            <a href="<?php echo e(route('it.users.index')); ?>" class="<?php echo e($btnSecondary); ?>">Kelola</a>
          </div>
        </div>
        <div class="<?php echo e($tableWrap); ?>">
          <table class="min-w-full divide-y divide-slate-100">
            <thead class="sticky top-0 z-10 bg-slate-50">
              <tr>
                <th class="<?php echo e($th); ?> w-12">#</th>
                <th class="<?php echo e($th); ?>">Nama</th>
                <th class="<?php echo e($th); ?>">Email</th>
                <th class="<?php echo e($th); ?> w-20 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
              <?php $__empty_1 = true; $__currentLoopData = $vendors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $vendor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-slate-50">
                  <td class="<?php echo e($td); ?> text-slate-500"><?php echo e($i + 1); ?></td>
                  <td class="<?php echo e($td); ?> font-medium text-slate-900"><?php echo e($vendor->name); ?></td>
                  <td class="<?php echo e($td); ?> break-all"><?php echo e($vendor->email); ?></td>
                  <td class="<?php echo e($td); ?> text-right">
                    <a href="<?php echo e(route('it.users.edit', $vendor->id)); ?>" class="inline-flex items-center rounded-md px-2 py-1 text-sm font-medium text-blue-600 hover:bg-blue-50 hover:text-blue-700">Edit</a>
                  </td>
                </tr>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                  <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada vendor.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </article>
    </section>

    <section class="grid grid-cols-1 gap-4 xl:grid-cols-2">
      <article class="<?php echo e($card); ?>">
        <form method="POST" action="<?php echo e(route('it.parameters.it.visibility')); ?>">
          <?php echo csrf_field(); ?>
          <div class="<?php echo e($cardHead); ?>">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
              <div>
                <h2 class="<?php echo e($cardTitle); ?>">Ditugaskan ke IT</h2>
                <p class="<?php echo e($cardHint); ?>">Pilih user IT yang tampil pada dropdown penugasan.</p>
              </div>
              <div class="flex flex-wrap gap-2">
                <a href="<?php echo e(route('it.users.index')); ?>" class="<?php echo e($btnSecondary); ?>">Kelola IT</a>
                <button type="submit" class="<?php echo e($btnPrimary); ?>">Simpan</button>
              </div>
            </div>
          </div>
          <div class="<?php echo e($tableWrap); ?>">
            <table class="min-w-full divide-y divide-slate-100">
              <thead class="sticky top-0 z-10 bg-slate-50">
                <tr>
                  <th class="<?php echo e($th); ?> w-12">#</th>
                  <th class="<?php echo e($th); ?>">Nama</th>
                  <th class="<?php echo e($th); ?>">Email</th>
                  <th class="<?php echo e($th); ?> w-28 text-center">Tampil</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 bg-white">
                <?php $__empty_1 = true; $__currentLoopData = $its; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                  <tr class="hover:bg-slate-50">
                    <td class="<?php echo e($td); ?> text-slate-500"><?php echo e($i + 1); ?></td>
                    <td class="<?php echo e($td); ?> font-medium text-slate-900"><?php echo e($it->name); ?></td>
                    <td class="<?php echo e($td); ?> break-all"><?php echo e($it->email); ?></td>
                    <td class="<?php echo e($td); ?> text-center">
                      <input type="checkbox" name="visible[]" value="<?php echo e($it->id); ?>" <?php if($it->visible_on_assign): echo 'checked'; endif; ?> class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    </td>
                  </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                  <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada user IT.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </form>
      </article>

      <article class="<?php echo e($card); ?>">
        <form method="POST" action="<?php echo e(route('it.parameters.ai_chat')); ?>">
          <?php echo csrf_field(); ?>
          <div class="<?php echo e($cardHead); ?>">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
              <div>
                <h2 class="<?php echo e($cardTitle); ?>">AI Chat Assistant</h2>
                <p class="<?php echo e($cardHint); ?>"><?php echo e($aiUserCount); ?> user diizinkan menggunakan AI chat.</p>
              </div>
              <button type="submit" class="<?php echo e($btnPrimary); ?>">Simpan</button>
            </div>

            <label class="mt-3 flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-3">
              <input type="hidden" name="ai_chat_enabled" value="0">
              <input type="checkbox" name="ai_chat_enabled" value="1" <?php if($aiChatEnabled ?? true): echo 'checked'; endif; ?> class="mt-0.5 h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
              <span class="text-sm text-slate-700">
                Tampilkan floating AI chat.
                <span class="block text-xs text-slate-500">Jika dimatikan, tombol AI disembunyikan dan endpoint chat menolak request.</span>
              </span>
            </label>
          </div>
          <div class="<?php echo e($tableWrap); ?>">
            <table class="min-w-full divide-y divide-slate-100">
              <thead class="sticky top-0 z-10 bg-slate-50">
                <tr>
                  <th class="<?php echo e($th); ?> w-12">#</th>
                  <th class="<?php echo e($th); ?>">Nama</th>
                  <th class="<?php echo e($th); ?>">Email</th>
                  <th class="<?php echo e($th); ?> w-24">Role</th>
                  <th class="<?php echo e($th); ?> w-28 text-center">Enable</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 bg-white">
                <?php $__empty_1 = true; $__currentLoopData = $usersForAiChat; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                  <tr class="hover:bg-slate-50">
                    <td class="<?php echo e($td); ?> text-slate-500"><?php echo e($i + 1); ?></td>
                    <td class="<?php echo e($td); ?> font-medium text-slate-900"><?php echo e($user->name); ?></td>
                    <td class="<?php echo e($td); ?> break-all"><?php echo e($user->email); ?></td>
                    <td class="<?php echo e($td); ?>"><?php echo e($user->role); ?></td>
                    <td class="<?php echo e($td); ?> text-center">
                      <input type="checkbox" name="ai_chat_users[]" value="<?php echo e($user->id); ?>" <?php if($user->ai_chat_enabled): echo 'checked'; endif; ?> class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    </td>
                  </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                  <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada user.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </form>
      </article>
    </section>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\helpdesk-app\resources\views/it/parameters.blade.php ENDPATH**/ ?>