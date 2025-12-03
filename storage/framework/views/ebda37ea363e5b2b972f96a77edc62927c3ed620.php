<?php $__env->startSection('title','Profil Saya'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Akun</h2>
        <div class="max-w-xl">
            <?php echo $__env->make('profile.partials.update-profile-information-form', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Ubah Password</h2>
        <div class="max-w-xl">
            <?php echo $__env->make('profile.partials.update-password-form', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>
    </div>

  
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\helpdesk-app\resources\views/profile/edit.blade.php ENDPATH**/ ?>