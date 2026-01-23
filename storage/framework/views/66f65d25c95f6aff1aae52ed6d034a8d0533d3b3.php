<?php $__env->startSection('title','Masuk'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
  <div class="w-full max-w-md">

    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-8" x-data="{ showPwd:false, submitting:false }">
      
     <div class="mb-6 text-center">
  <img
    src="<?php echo e(asset('images/helpdesk.png')); ?>"   
    alt="PT BPR BKK Jateng"
    class="mx-auto h-32 w-auto md:h-40"     
    loading="eager"
  />
  <h1 class="mt-3 text-xl md:text-2xl font-semibold text-gray-800">
    Login - Sambatan
  </h1>
   
  <p class="mt-2 text-sm text-gray-600 leading-relaxed">
    Sistem Penyampaian Bantuan Tiketing Operasional
  </p>
</div>

      
      <?php if(session('status')): ?>
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-700 px-4 py-3">
          <?php echo e(session('status')); ?>

        </div>
      <?php endif; ?>

      
      <?php if($errors->any()): ?>
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 text-red-700 px-4 py-3">
          <ul class="list-disc list-inside space-y-1">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="POST"
            action="<?php echo e(route('login')); ?>"
            class="space-y-4"
            x-on:submit="submitting=true">
        <?php echo csrf_field(); ?>

        
        <div>
  <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
  <input id="username"
         type="text"
         name="username"
         value="<?php echo e(old('username')); ?>"
         required
         autofocus
         autocomplete="username"
         class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
         aria-invalid="<?php $__errorArgs = ['username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> true <?php else: ?> false <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
         aria-describedby="username-error">
  <?php $__errorArgs = ['username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
    <p id="username-error" class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
  <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
</div>

        
        <div>
          <div class="flex items-center justify-between">
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi</label>
            <?php if(Route::has('password.request')): ?>
             
            <?php endif; ?>
          </div>

          <div class="relative">
            <input :type="showPwd ? 'text' : 'password'"
                   id="password"
                   name="password"
                   required
                   autocomplete="current-password"
                   class="w-full rounded-lg border-gray-300 pr-10 focus:border-indigo-500 focus:ring-indigo-500"
                   aria-invalid="<?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> true <?php else: ?> false <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                   aria-describedby="password-error">
            <button type="button"
                    class="absolute inset-y-0 right-0 px-3 text-gray-500 hover:text-gray-700"
                    x-on:click="showPwd = !showPwd"
                    :aria-label="showPwd ? 'Sembunyikan sandi' : 'Tampilkan sandi'">
              <span x-show="!showPwd">👁️</span>
              <span x-show="showPwd">🙈</span>
            </button>
          </div>
          <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <p id="password-error" class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
          <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        
        <div class="flex items-center justify-between">
          <label class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox"
                   name="remember"
                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                   <?php if(old('remember')): echo 'checked'; endif; ?>>
            Ingat saya
          </label>

          
        </div>

        
        <button
          class="w-full inline-flex items-center justify-center rounded-lg bg-gray-900 px-4 py-2.5 text-white hover:bg-gray-800 disabled:opacity-60"
          :disabled="submitting">
          <span x-show="!submitting">Masuk</span>
          <span x-show="submitting">Memproses…</span>
        </button>
      </form>
    </div>

    <p class="mt-4 text-center text-xs text-gray-500">
      © <?php echo e(date('Y')); ?> Helpdesk By Bidang TI
    </p>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.guest', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\helpdesk-app\resources\views/auth/login.blade.php ENDPATH**/ ?>