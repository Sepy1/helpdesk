
<?php $__env->startSection('title','Daftar Tiket'); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-3 sm:p-5 text-xs sm:text-sm">
  
  <div class="flex flex-col gap-3 md:gap-4">
    <div class="flex items-center justify-center text-center">
      <div>
        <h2 class="text-lg font-semibold text-gray-800">Daftar Tiket</h2>
        <p class="text-sm text-gray-500">Klik nomor tiket untuk melihat detail.</p>
      </div>
    </div>

    
    <form method="GET" class="w-full flex flex-col md:flex-row items-end justify-center gap-2 md:gap-2 md:flex-nowrap overflow-x-auto mb-4 md:mb-6" id="filter-form">
      <div class="order-1 md:order-none shrink-0 w-full md:w-[360px]">
        <input type="text" name="q" value="<?php echo e(request('q')); ?>" placeholder="Cari nomor / deskripsi / kategori"
               class="w-full h-10 rounded-lg border-gray-300 px-3 focus:border-indigo-500 focus:ring-indigo-500" />
      </div>

      
      <div class="order-2 md:order-none shrink-0 w-full md:w-[220px]">
        <label class="sr-only">Root Cause</label>
        <select name="root_cause" class="w-full h-10 rounded-lg border-gray-300 px-3 focus:border-indigo-500 focus:ring-indigo-500">
          <option value="">Root Cause</option>
          <?php $__currentLoopData = $rootCauses ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($rc); ?>" <?php if(request('root_cause')===$rc): echo 'selected'; endif; ?>><?php echo e($rc); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>

      
      <div class="order-4 md:order-none shrink-0 w-full md:w-[150px]">
        <label class="sr-only">Status</label>
        <select name="status" class="w-full h-10 rounded-lg border-gray-300 px-3 focus:border-indigo-500 focus:ring-indigo-500">
          <option value="">Status</option>
          <?php $__currentLoopData = ['OPEN','ON_PROGRESS','ESKALASI_VENDOR','VENDOR_RESOLVED','CLOSED']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($s); ?>" <?php if(request('status')===$s): echo 'selected'; endif; ?>><?php echo e($s); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>

      
      <div class="shrink-0 flex flex-col md:flex-row gap-2 w-full md:w-auto">
        <div class="order-2 md:order-none shrink-0 w-full md:w-[150px]">
          <input type="text" name="date_from" value="<?php echo e(request('date_from')); ?>" placeholder="Tgl Awal" inputmode="numeric" pattern="\d{4}-\d{2}-\d{2}"
                 class="w-full h-10 rounded-lg border-gray-300 px-3 focus:border-indigo-500 focus:ring-indigo-500"
                 onfocus="this.type='date'" onblur="if(!this.value) this.type='text'" />
        </div>
        <div class="order-3 md:order-none shrink-0 w-full md:w-[150px]">
          <input type="text" name="date_to" value="<?php echo e(request('date_to')); ?>" placeholder="Tgl Akhir" inputmode="numeric" pattern="\d{4}-\d{2}-\d{2}"
                 class="w-full h-10 rounded-lg border-gray-300 px-3 focus:border-indigo-500 focus:ring-indigo-500"
                 onfocus="this.type='date'" onblur="if(!this.value) this.type='text'" />
        </div>
      </div>

      
      <div class="order-5 md:order-none shrink-0 flex gap-2 justify-center w-full md:w-auto">
        <button type="submit" class="w-full md:w-auto h-10 rounded-lg bg-gradient-to-r from-blue-500 to-sky-500 text-white px-4">Filter</button>

        <?php if(request()->hasAny(['q','status','date_from','date_to'])): ?>
          <a href="<?php echo e(route('it.dashboard')); ?>"
             class="shrink-0 h-10 inline-block text-center rounded-lg border border-gray-200 px-4 text-sm text-gray-700 hover:underline leading-10">
             Reset
           </a>
            <a href="<?php echo e(route('it.tickets.export', request()->query())); ?>"
              class="shrink-0 h-10 inline-block text-center rounded-lg bg-emerald-600 px-4 text-white hover:bg-emerald-700 leading-10">
             Export Result
           </a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <?php echo $__env->make('it._tickets', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>


<script>
// Tidak perlu JS untuk subkategori; pencarian digabung dalam kolom q
</script>
<script>
  // Polling: fetch tickets fragment and replace content if changed
  (function(){
    const intervalMs = 3000; // 10s
    const fragmentUrl = '<?php echo e(route("it.tickets.fragment")); ?>' + window.location.search;
    async function fetchFragment(){
      try{
        const res = await fetch(fragmentUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if(!res.ok) return;
        const html = await res.text();
        // create a container to parse returned HTML
        const tmp = document.createElement('div'); tmp.innerHTML = html;
        const newDesktop = tmp.querySelector('#tickets-fragment');
        const newMobile = tmp.querySelector('#tickets-fragment-mobile');
        const newPag = tmp.querySelector('#tickets-fragment-pagination');
        if(newDesktop){
          const oldDesktop = document.querySelector('#tickets-fragment');
          oldDesktop?.replaceWith(newDesktop);
        }
        if(newMobile){
          const oldMobile = document.querySelector('#tickets-fragment-mobile');
          oldMobile?.replaceWith(newMobile);
        }
        if(newPag){
          const oldPag = document.querySelector('#tickets-fragment-pagination');
          oldPag?.replaceWith(newPag);
        }
      }catch(e){
        // ignore errors
      }
    }
    // start polling after small delay
    setTimeout(() => { fetchFragment(); setInterval(fetchFragment, intervalMs); }, 3000);
  })();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\helpdesk-app\resources\views/it/dashboard.blade.php ENDPATH**/ ?>