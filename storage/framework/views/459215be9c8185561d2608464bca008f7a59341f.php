<?php $__env->startSection('title','Parameter'); ?>

<?php $__env->startSection('content'); ?>
<?php
  $categories = $categories ?? collect();
  $rootCauses = $rootCauses ?? collect();
  $its = $its ?? collect();
  $usersForAiChat = $usersForAiChat ?? collect();

  $categoryCount = $categories->count();
  $subcategoryCount = $categories->sum(fn ($category) => $category->subcategories->count());
  $requestTypeCount = $categories->sum(fn ($category) => $category->subcategories->sum(fn ($subcategory) => $subcategory->requestTypes->count()));
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

    <section class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
      <div class="<?php echo e($card); ?> p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Kategori</p>
        <p class="mt-2 text-2xl font-semibold text-slate-950"><?php echo e($categoryCount); ?></p>
      </div>
      <div class="<?php echo e($card); ?> p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Subkategori</p>
        <p class="mt-2 text-2xl font-semibold text-slate-950"><?php echo e($subcategoryCount); ?></p>
      </div>
      <div class="<?php echo e($card); ?> p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Jenis permintaan</p>
        <p class="mt-2 text-2xl font-semibold text-slate-950"><?php echo e($requestTypeCount); ?></p>
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
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">IT tampil</p>
        <p class="mt-2 text-2xl font-semibold text-slate-950"><?php echo e($visibleItCount); ?></p>
      </div>
    </section>

    <?php
      $themeOptions = [
        'blue' => ['label' => 'Blue', 'swatch' => 'bg-blue-600', 'ring' => 'peer-checked:ring-blue-500'],
        'emerald' => ['label' => 'Emerald', 'swatch' => 'bg-emerald-600', 'ring' => 'peer-checked:ring-emerald-500'],
        'rose' => ['label' => 'Rose', 'swatch' => 'bg-rose-600', 'ring' => 'peer-checked:ring-rose-500'],
        'violet' => ['label' => 'Violet', 'swatch' => 'bg-violet-600', 'ring' => 'peer-checked:ring-violet-500'],
        'amber' => ['label' => 'Amber', 'swatch' => 'bg-amber-500', 'ring' => 'peer-checked:ring-amber-500'],
        'midnight' => ['label' => 'Midnight', 'swatch' => 'bg-gradient-to-br from-indigo-950 via-slate-900 to-purple-950', 'ring' => 'peer-checked:ring-indigo-700'],
        'obsidian' => ['label' => 'Obsidian', 'swatch' => 'bg-gradient-to-br from-zinc-700 via-zinc-950 to-black', 'ring' => 'peer-checked:ring-zinc-600'],
        'deep_navy' => ['label' => 'Deep Navy', 'swatch' => 'bg-gradient-to-br from-blue-800 via-slate-900 to-cyan-950', 'ring' => 'peer-checked:ring-blue-700'],
        'dark_forest' => ['label' => 'Dark Forest', 'swatch' => 'bg-gradient-to-br from-emerald-800 via-green-950 to-slate-950', 'ring' => 'peer-checked:ring-emerald-700'],
        'burgundy' => ['label' => 'Burgundy', 'swatch' => 'bg-gradient-to-br from-rose-800 via-red-950 to-slate-950', 'ring' => 'peer-checked:ring-rose-700'],
      ];
    ?>
    <section class="<?php echo e($card); ?>">
      <form method="POST" action="<?php echo e(route('it.parameters.theme')); ?>">
        <?php echo csrf_field(); ?>
        <div class="<?php echo e($cardHead); ?> flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <div class="flex flex-wrap items-center gap-2">
              <h2 class="<?php echo e($cardTitle); ?>">Tema Default Aplikasi</h2>
              <span class="rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-700">Global</span>
            </div>
            <p class="<?php echo e($cardHint); ?>">Tema default akan digunakan setiap kali seluruh user mengakses aplikasi.</p>
          </div>
          <button type="submit" class="<?php echo e($btnPrimary); ?>">Jadikan Tema Default</button>
        </div>
        <div class="grid grid-cols-2 gap-3 p-4 sm:grid-cols-5 sm:p-5">
          <?php $__currentLoopData = $themeOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $theme): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <label class="cursor-pointer">
              <input type="radio" name="theme" value="<?php echo e($value); ?>" class="peer sr-only" <?php if(($appTheme ?? 'blue') === $value): echo 'checked'; endif; ?>>
              <span class="relative flex items-center gap-3 rounded-lg border border-slate-200 bg-white p-3 shadow-sm transition hover:bg-slate-50 peer-checked:border-transparent peer-checked:ring-2 <?php echo e($theme['ring']); ?>">
                <span class="h-7 w-7 shrink-0 rounded-full <?php echo e($theme['swatch']); ?> shadow-sm"></span>
                <span class="text-sm font-medium text-slate-700"><?php echo e($theme['label']); ?></span>
                <?php if(($appTheme ?? 'blue') === $value): ?>
                  <span class="ml-auto text-[10px] font-semibold uppercase tracking-wide text-blue-600">Default</span>
                <?php endif; ?>
              </span>
            </label>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
      </form>
    </section>

    <section class="grid grid-cols-1 gap-4 xl:grid-cols-2">
      <?php echo $__env->make('it.partials.ticket-classification-settings', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

      

      <?php echo $__env->make('it.partials.root-cause-tree-settings', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

      
    </section>

    <section class="grid grid-cols-1 gap-4 xl:grid-cols-3">
      

      
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