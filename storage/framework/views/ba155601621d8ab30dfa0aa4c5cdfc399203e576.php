<?php $__env->startSection('title','Daftar Tiket'); ?>

<?php $__env->startPush('styles'); ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-3 sm:p-5 text-xs sm:text-sm">
  
  <div class="flex flex-col gap-3 md:gap-4">
    <div class="flex items-center justify-center text-center">
      <div>
        <h2 class="text-lg font-semibold text-gray-800">Daftar Tiket</h2>
        <p class="text-sm text-gray-500">Klik nomor tiket untuk melihat detail.</p>
      </div>
    </div>

    
    <form method="GET" class="w-full flex flex-col xl:flex-row xl:flex-nowrap items-end gap-2 mb-4 md:mb-6" id="filter-form">
      <div class="w-full xl:w-[260px] xl:shrink-0">
        <input type="text" name="q" value="<?php echo e(request('q')); ?>" placeholder="Cari nomor / deskripsi / kategori"
               class="w-full h-10 rounded-lg border-gray-300 px-3 focus:border-indigo-500 focus:ring-indigo-500" />
      </div>

      
      <div class="w-full xl:w-[170px] xl:shrink-0">
        <input type="text" name="username" value="<?php echo e(request('username')); ?>" placeholder="Username pembuat"
               class="w-full h-10 rounded-lg border-gray-300 px-3 focus:border-indigo-500 focus:ring-indigo-500" />
      </div>

      
      <div class="w-full xl:w-[170px] xl:shrink-0">
        <label class="sr-only">Root Cause</label>
        <select name="root_cause" class="w-full h-10 rounded-lg border-gray-300 px-3 focus:border-indigo-500 focus:ring-indigo-500">
          <option value="">Root Cause</option>
          <?php $__currentLoopData = $rootCauses ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($rc); ?>" <?php if(request('root_cause')===$rc): echo 'selected'; endif; ?>><?php echo e($rc); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>

      
      <div class="w-full xl:w-[130px] xl:shrink-0">
        <label class="sr-only">Status</label>
        <select name="status" class="w-full h-10 rounded-lg border-gray-300 px-3 focus:border-indigo-500 focus:ring-indigo-500">
          <option value="">Status</option>
          <?php $__currentLoopData = ['OPEN','ON_PROGRESS','ESKALASI_VENDOR','VENDOR_RESOLVED','CLOSED']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($s); ?>" <?php if(request('status')===$s): echo 'selected'; endif; ?>><?php echo e($s); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>

      
      <div class="w-full xl:w-[260px] xl:shrink-0">
        <label class="sr-only">Periode Tanggal</label>
        <input type="text" id="date-range" name="date_range"
               value="<?php echo e(request('date_from') && request('date_to') ? request('date_from').' - '.request('date_to') : ''); ?>"
               placeholder="Periode Tanggal (YYYY-MM-DD - YYYY-MM-DD)"
               class="w-full h-10 rounded-lg border-gray-300 px-3 focus:border-indigo-500 focus:ring-indigo-500" />
        <input type="hidden" name="date_from" id="date-from-hidden" value="<?php echo e(request('date_from')); ?>">
        <input type="hidden" name="date_to" id="date-to-hidden" value="<?php echo e(request('date_to')); ?>">
      </div>

      
      <div class="w-full xl:flex-1 flex flex-wrap xl:flex-nowrap gap-2 justify-start xl:justify-end">
        <button type="submit" class="w-full md:w-auto h-10 rounded-lg bg-gradient-to-r from-blue-500 to-sky-500 text-white px-4">Filter</button>

        <?php if(request()->hasAny(['q','username','status','date_from','date_to','root_cause','category_id','subcategory_id','kategori'])): ?>
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
// Periode tanggal 1 field -> parse ke date_from dan date_to (fallback manual input)
(function () {
  const form = document.getElementById('filter-form');
  const rangeInput = document.getElementById('date-range');
  const fromInput = document.getElementById('date-from-hidden');
  const toInput = document.getElementById('date-to-hidden');
  if (!form || !rangeInput || !fromInput || !toInput) return;

  form.addEventListener('submit', function () {
    const raw = (rangeInput.value || '').trim();
    if (!raw) {
      fromInput.value = '';
      toInput.value = '';
      return;
    }

    // dukung pemisah: " - ", " s/d ", atau " to "
    const parts = raw.split(/\s+-\s+|\s+s\/d\s+|\s+to\s+/i).map(s => s.trim()).filter(Boolean);
    if (parts.length >= 2) {
      fromInput.value = parts[0];
      toInput.value = parts[1];
    } else {
      // jika hanya 1 tanggal diisi, gunakan sebagai awal & akhir
      fromInput.value = parts[0] || '';
      toInput.value = parts[0] || '';
    }
  });
})();
</script>
<script>
  // Polling: fetch tickets fragment and replace content if changed
  (function(){
    const intervalMs = 3000; // 3s
    const activeFilterKeys = ['q', 'username', 'status', 'date_from', 'date_to', 'root_cause', 'category_id', 'subcategory_id', 'kategori'];
    const queryParams = new URLSearchParams(window.location.search);
    const hasActiveFilter = activeFilterKeys.some((key) => {
      const value = queryParams.get(key);
      return value !== null && String(value).trim() !== '';
    });

    // Saat filter aktif, jangan auto-refresh list agar hasil filter tidak ketimpa polling.
    if (hasActiveFilter) return;

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

<?php $__env->startPush('scripts'); ?>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const rangeInput = document.getElementById('date-range');
      const fromInput = document.getElementById('date-from-hidden');
      const toInput = document.getElementById('date-to-hidden');
      if (!rangeInput || !fromInput || !toInput || typeof flatpickr !== 'function') return;

      flatpickr(rangeInput, {
        mode: 'range',
        dateFormat: 'Y-m-d',
        allowInput: true,
        defaultDate: (fromInput.value && toInput.value) ? [fromInput.value, toInput.value] : null,
        onChange: function (selectedDates, dateStr, instance) {
          if (selectedDates.length === 2) {
            fromInput.value = instance.formatDate(selectedDates[0], 'Y-m-d');
            toInput.value = instance.formatDate(selectedDates[1], 'Y-m-d');
            return;
          }

          if (selectedDates.length === 1) {
            const single = instance.formatDate(selectedDates[0], 'Y-m-d');
            fromInput.value = single;
            toInput.value = single;
            return;
          }

          fromInput.value = '';
          toInput.value = '';
        }
      });
    });
  </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\helpdesk-app\resources\views/it/dashboard.blade.php ENDPATH**/ ?>