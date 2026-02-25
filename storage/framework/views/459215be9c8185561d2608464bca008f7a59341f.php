
<?php $__env->startSection('title','Parameter'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto p-6">
  <h1 class="text-2xl font-semibold mb-4">Parameter</h1>

  <?php if(session('success')): ?>
    <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800"><?php echo e(session('success')); ?></div>
  <?php endif; ?>

  <div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      
      <div class="bg-white rounded shadow overflow-hidden">
        <div class="flex items-center justify-between p-4 border-b">
          <div class="text-lg font-medium">Kategori</div>
          <form method="POST" action="<?php echo e(route('it.parameters.category.store')); ?>" class="flex items-center gap-2">
            <?php echo csrf_field(); ?>
            <input name="name" required class="rounded border px-3 py-1 text-sm" placeholder="Nama kategori" />
            <button class="px-3 py-1 bg-sky-600 text-white rounded text-sm">Tambah</button>
          </form>
        </div>

        <div class="p-4 overflow-x-auto max-h-52 overflow-y-auto">
          <table class="w-full text-sm">
            <thead class="text-left text-xs text-gray-500 border-b">
              <tr>
                <th class="py-2">#</th>
                <th class="py-2">Nama Kategori</th>
                <th class="py-2">Jumlah Subkategori</th>
                <th class="py-2">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y">
              <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="hover:bg-gray-50">
                  <td class="py-2"><?php echo e($i + 1); ?></td>
                  <td class="py-2 font-medium"><?php echo e($c->name); ?></td>
                  <td class="py-2"><?php echo e($c->subcategories->count()); ?></td>
                  <td class="py-2">
                    <div class="flex gap-2">
                      <form method="POST" action="<?php echo e(route('it.parameters.category.delete', $c->id)); ?>" onsubmit="return confirm('Hapus kategori?');">
                        <?php echo csrf_field(); ?>
                        <button class="text-red-600 text-sm">Hapus</button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
          </table>
        </div>
      </div>

      
      <div class="bg-white rounded shadow overflow-hidden">
        <div class="flex items-center justify-between p-4 border-b">
          <div class="text-lg font-medium">Sub</div>
          <form method="POST" action="<?php echo e(route('it.parameters.subcategory.store')); ?>" class="flex items-center gap-2">
            <?php echo csrf_field(); ?>
            <select name="category_id" required class="rounded border px-3 py-1 text-sm">
              <option value="">Pilih Kategori</option>
              <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <input name="name" required class="rounded border px-3 py-1 text-sm" placeholder="Nama subkategori" />
            <button class="px-3 py-1 bg-sky-600 text-white rounded text-sm">Tambah</button>
          </form>
        </div>

        <div class="p-4 overflow-x-auto max-h-52 overflow-y-auto">
          <table class="w-full text-sm">
            <thead class="text-left text-xs text-gray-500 border-b">
              <tr>
                <th class="py-2">#</th>
                <th class="py-2">Nama Subkategori</th>
                <th class="py-2">Kategori Induk</th>
                <th class="py-2">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y">
              <?php $count = 0; ?>
              <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $__currentLoopData = $c->subcategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <?php $count++; ?>
                  <tr class="hover:bg-gray-50">
                    <td class="py-2"><?php echo e($count); ?></td>
                    <td class="py-2"><?php echo e($s->name); ?></td>
                    <td class="py-2"><?php echo e($c->name); ?></td>
                    <td class="py-2">
                      <form method="POST" action="<?php echo e(route('it.parameters.subcategory.delete', $s->id)); ?>" onsubmit="return confirm('Hapus subkategori?');">
                        <?php echo csrf_field(); ?>
                        <button class="text-red-600 text-sm">Hapus</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              <?php if($count === 0): ?>
                <tr><td colspan="4" class="py-4 text-center text-gray-500">Belum ada subkategori</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="bg-white rounded shadow overflow-hidden">
        <div class="flex items-center justify-between p-4 border-b">
          <div class="text-lg font-medium">Root Causes</div>
          <form method="POST" action="<?php echo e(route('it.parameters.rootcause.store')); ?>" class="flex items-center gap-2">
            <?php echo csrf_field(); ?>
            <input name="name" required class="rounded border px-3 py-1 text-sm" placeholder="Root cause" />
            <button class="px-3 py-1 bg-sky-600 text-white rounded text-sm">Tambah</button>
          </form>
        </div>

        <div class="p-4 overflow-x-auto max-h-52 overflow-y-auto">
          <table class="w-full text-sm">
            <thead class="text-left text-xs text-gray-500 border-b">
              <tr>
                <th class="py-2">#</th>
                <th class="py-2">Root Cause</th>
                <th class="py-2">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y">
              <?php $__currentLoopData = $rootCauses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $rc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="hover:bg-gray-50">
                  <td class="py-2"><?php echo e($i + 1); ?></td>
                  <td class="py-2"><?php echo e($rc->name); ?></td>
                  <td class="py-2">
                    <form method="POST" action="<?php echo e(route('it.parameters.rootcause.delete', $rc->id)); ?>" onsubmit="return confirm('Hapus root cause?');">
                      <?php echo csrf_field(); ?>
                      <button class="text-red-600 text-sm">Hapus</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              <?php if(count($rootCauses) === 0): ?>
                <tr><td colspan="3" class="py-4 text-center text-gray-500">Belum ada root cause</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="bg-white rounded shadow overflow-hidden">
        <div class="flex items-center justify-between p-4 border-b">
          <div class="text-lg font-medium">Daftar Vendor</div>
          <a href="<?php echo e(route('it.users.index')); ?>" class="px-3 py-1 bg-sky-600 text-white rounded text-sm">Kelola Vendor</a>
        </div>

        <div class="p-4 overflow-x-auto max-h-52 overflow-y-auto">
          <table class="w-full text-sm">
            <thead class="text-left text-xs text-gray-500 border-b">
              <tr>
                <th class="py-2">#</th>
                <th class="py-2">Nama</th>
                <th class="py-2">Email</th>
                <th class="py-2">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y">
              <?php $__currentLoopData = $vendors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="hover:bg-gray-50">
                  <td class="py-2"><?php echo e($i + 1); ?></td>
                  <td class="py-2 font-medium"><?php echo e($v->name); ?></td>
                  <td class="py-2"><?php echo e($v->email); ?></td>
                  <td class="py-2">
                    <a href="<?php echo e(route('it.users.edit', $v->id)); ?>" class="text-sky-600 text-sm">Edit</a>
                  </td>
                </tr>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              <?php if(count($vendors) === 0): ?>
                <tr><td colspan="4" class="py-4 text-center text-gray-500">Belum ada vendor</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\helpdesk-app\resources\views/it/parameters.blade.php ENDPATH**/ ?>