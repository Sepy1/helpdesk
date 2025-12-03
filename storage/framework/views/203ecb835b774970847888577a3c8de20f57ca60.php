
<?php $__env->startSection('title','Manajemen User'); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-lg font-semibold text-gray-800">Manajemen User</h2>
    <a href="<?php echo e(route('it.users.create')); ?>" class="rounded-lg bg-emerald-600 px-3 py-2 text-white hover:bg-emerald-700">Tambah User</a>
  </div>

  <form method="GET" action="<?php echo e(route('it.users.index')); ?>" class="mb-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
      <div>
        <input type="text" name="q" value="<?php echo e(request('q')); ?>" placeholder="Cari nama / email / username" class="w-full rounded-lg h-10 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
      </div>
      <div>
        <select name="role" class="w-full rounded-lg h-10 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
          <option value="">Semua Role</option>
          <?php $__currentLoopData = ['IT','CABANG','VENDOR','ADMIN']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($r); ?>" <?php if(request('role')===$r): echo 'selected'; endif; ?>><?php echo e($r); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div class="flex md:justify-end">
        <button class="w-full md:w-auto rounded-lg h-10 bg-indigo-600 px-4 text-white hover:bg-indigo-700">Filter</button>
      </div>
    </div>
  </form>

  <?php if(session('success')): ?>
    <div class="mb-4 rounded-lg bg-green-50 text-green-700 px-4 py-3 border border-green-200 shadow-md"><?php echo e(session('success')); ?></div>
  <?php endif; ?>
  <?php if(session('error')): ?>
    <div class="mb-4 rounded-lg bg-red-50 text-red-700 px-4 py-3 border border-red-200 shadow-md"><?php echo e(session('error')); ?></div>
  <?php endif; ?>

  <div class="overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-gray-50 text-gray-600">
        <tr>
          <th class="py-2 px-3 text-left">Nama</th>
          <th class="py-2 px-3 text-left">Email</th>
          <th class="py-2 px-3 text-left">Username</th>
          <th class="py-2 px-3 text-left">Role</th>
          <th class="py-2 px-3 text-left">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
          <td class="py-2 px-3"><?php echo e($u->name); ?></td>
          <td class="py-2 px-3"><?php echo e($u->email); ?></td>
          <td class="py-2 px-3"><?php echo e($u->username); ?></td>
          <td class="py-2 px-3"><?php echo e($u->role); ?></td>
          <td class="py-2 px-3">
            <div class="flex flex-col sm:flex-row gap-2">
              <a href="<?php echo e(route('it.users.edit',$u)); ?>" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-white hover:bg-indigo-700 text-center">Edit</a>
              <form method="POST" action="<?php echo e(route('it.users.destroy',$u)); ?>" onsubmit="return confirm('Hapus user ini?')">
                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                <button class="w-full sm:w-auto rounded-lg bg-red-600 px-3 py-1.5 text-white hover:bg-red-700">Delete</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </tbody>
    </table>
  </div>

  <div class="mt-4"><?php echo $users->links('pagination::tailwind'); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\helpdesk-app\resources\views/it/users/index.blade.php ENDPATH**/ ?>