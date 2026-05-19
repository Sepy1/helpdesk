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
        <p class="text-sm text-gray-500">Klik nomor tiket untuk melihat detail. Filter diterapkan otomatis.</p>
      </div>
    </div>

    
    <form method="GET" class="w-full flex flex-col xl:flex-row xl:flex-nowrap items-end gap-2 mb-4 md:mb-6" id="filter-form">
      <div class="w-full xl:w-[260px] xl:shrink-0">
        <input type="text" id="filter-q" name="q" value="<?php echo e(request('q')); ?>" placeholder="Cari nomor / deskripsi / kategori"
               class="w-full h-10 rounded-lg border-gray-300 px-3 focus:border-indigo-500 focus:ring-indigo-500" autocomplete="off" />
      </div>

      
      <div class="w-full min-w-0 xl:w-[220px] xl:shrink-0">
        <label class="sr-only">Kode kantor pembuat</label>
        <select name="kode_kantor" class="w-full h-10 rounded-lg border-gray-300 px-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
          <option value="">Semua kantor</option>
          <?php $__currentLoopData = $kodeKantors ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($kk->kode); ?>" <?php if(request('kode_kantor') === $kk->kode): echo 'selected'; endif; ?>><?php echo e($kk->kode); ?> — <?php echo e($kk->nama_kantor); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
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
        <button type="submit" class="w-full md:w-auto h-10 rounded-lg border border-gray-200 bg-white px-4 text-sm text-gray-700 hover:bg-gray-50" title="Muat ulang dengan nilai filter saat ini">Muat ulang</button>

        <a href="<?php echo e(route('it.dashboard')); ?>"
           class="shrink-0 h-10 inline-block text-center rounded-lg border border-gray-200 px-4 text-sm text-gray-700 hover:underline leading-10">
           Reset
         </a>
        <a href="<?php echo e(route('it.tickets.export', request()->query())); ?>"
            class="shrink-0 h-10 inline-block text-center rounded-lg hd-btn-export leading-10">
           Export Result
         </a>
      </div>
    </form>
  </div>

  <?php echo $__env->make('it._tickets', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>


<script>
// Parse periode tanggal (teks range) -> hidden date_from / date_to (dipakai submit manual & otomatis)
(function () {
  const form = document.getElementById('filter-form');
  const rangeInput = document.getElementById('date-range');
  const fromInput = document.getElementById('date-from-hidden');
  const toInput = document.getElementById('date-to-hidden');
  if (!form || !rangeInput || !fromInput || !toInput) return;

  window.__parseTicketDashboardDates = function () {
    const raw = (rangeInput.value || '').trim();
    if (!raw) {
      fromInput.value = '';
      toInput.value = '';
      return;
    }
    const parts = raw.split(/\s+-\s+|\s+s\/d\s+|\s+to\s+/i).map(function (s) { return s.trim(); }).filter(Boolean);
    if (parts.length >= 2) {
      fromInput.value = parts[0];
      toInput.value = parts[1];
    } else {
      fromInput.value = parts[0] || '';
      toInput.value = parts[0] || '';
    }
  };

  form.addEventListener('submit', function () {
    window.__parseTicketDashboardDates();
  });
})();
</script>
<script>
  // Polling: fetch tickets fragment and replace content if changed
  (function(){
    const intervalMs = 3000; // 3s
    const activeFilterKeys = ['q', 'kode_kantor', 'status', 'date_from', 'date_to', 'root_cause', 'category_id', 'subcategory_id', 'kategori'];
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
      const form = document.getElementById('filter-form');
      const rangeInput = document.getElementById('date-range');
      const fromInput = document.getElementById('date-from-hidden');
      const toInput = document.getElementById('date-to-hidden');
      const qInput = document.getElementById('filter-q');
      if (!form || !rangeInput || !fromInput || !toInput || typeof flatpickr !== 'function') return;

      function applyFiltersFromForm() {
        if (typeof window.__parseTicketDashboardDates === 'function') {
          window.__parseTicketDashboardDates();
        }
        if (typeof form.requestSubmit === 'function') {
          form.requestSubmit();
        } else {
          form.submit();
        }
      }

      let qDebounce;
      if (qInput) {
        qInput.addEventListener('input', function () {
          clearTimeout(qDebounce);
          qDebounce = setTimeout(function () { applyFiltersFromForm(); }, 450);
        });
      }

      form.querySelectorAll('select').forEach(function (sel) {
        sel.addEventListener('change', function () { applyFiltersFromForm(); });
      });

      rangeInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          applyFiltersFromForm();
        }
      });

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
        },
        onClose: function () {
          applyFiltersFromForm();
        }
      });
    });
  </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\helpdesk-app\resources\views/it/dashboard.blade.php ENDPATH**/ ?>