<?php $__env->startSection('title', 'Board CR'); ?>

<?php $__env->startSection('content'); ?>
<?php
  $columns = [
    ['key' => 'open', 'label' => 'Backlog'],
    ['key' => 'vendor_development', 'label' => 'On Progress'],
    ['key' => 'uat', 'label' => 'Testing UAT'],
    ['key' => 'go_live', 'label' => 'Go Live'],
    ['key' => 'closed', 'label' => 'Done'],
  ];
?>

<div class="min-h-[calc(100vh-9rem)] rounded-3xl border border-white/10 bg-gradient-to-br from-slate-950 via-indigo-950 to-slate-900 p-4 sm:p-5 text-slate-100 shadow-2xl shadow-indigo-950/20">
  <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div>
      <p class="text-xs uppercase tracking-[0.35em] text-cyan-300/80">IT Workspace</p>
      <h1 class="mt-2 text-2xl font-semibold tracking-tight">Dashboard CR (Board)</h1>
      <p class="mt-2 max-w-3xl text-sm text-slate-300">
        Board ini mengambil data CR eksternal dari endpoint manpro dan mendukung drag-drop untuk update status.
      </p>
      <p class="mt-2 text-xs text-slate-400">
        Endpoint: <span class="font-mono text-slate-200"><?php echo e(config('services.extern_cr.base_url')); ?><?php echo e(config('services.extern_cr.dashboard_path')); ?></span>
      </p>
    </div>

    <div class="flex gap-2">
      <button type="button" id="reloadBoard" class="rounded-xl border border-white/15 bg-white/5 px-4 py-2 text-sm font-medium text-white hover:bg-white/10">
        Muat Ulang
      </button>
      <a href="<?php echo e(route('it.dashboard')); ?>" class="rounded-xl bg-sky-500 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-400">
        Kembali ke Tiket
      </a>
    </div>
  </div>

  <div id="boardNotice" class="mt-4 hidden rounded-2xl border border-red-400/30 bg-red-500/10 px-4 py-3 text-sm text-red-100"></div>
  <div id="boardMeta" class="mt-4 text-xs text-slate-400"></div>

  <div class="mt-5 grid gap-4 xl:grid-cols-5">
    <?php $__currentLoopData = $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <section class="board-column rounded-2xl border border-white/10 bg-white/5 p-3" data-status="<?php echo e($column['key']); ?>">
        <div class="mb-3 flex items-center justify-between">
          <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-100"><?php echo e($column['label']); ?></h2>
          <span class="rounded-full bg-white/10 px-2 py-1 text-xs font-semibold text-slate-200" data-count-for="<?php echo e($column['key']); ?>">0</span>
        </div>
        <div class="board-dropzone min-h-[18rem] space-y-3 rounded-2xl border border-dashed border-white/10 bg-black/10 p-2 transition-colors" data-dropzone="<?php echo e($column['key']); ?>">
          <div class="board-empty rounded-xl border border-dashed border-white/10 px-3 py-4 text-center text-sm text-slate-400">
            Belum ada CR.
          </div>
        </div>
      </section>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>
</div>

<div id="crModal" class="fixed inset-0 z-[999] hidden items-center justify-center bg-slate-950/75 px-4">
  <div class="w-full max-w-2xl rounded-3xl border border-white/10 bg-slate-950 p-5 text-slate-100 shadow-2xl">
    <div class="flex items-start justify-between gap-4">
      <div>
        <p id="modalStatus" class="text-xs uppercase tracking-[0.3em] text-cyan-300"></p>
        <h3 id="modalTitle" class="mt-2 text-xl font-semibold"></h3>
        <p id="modalNumber" class="mt-1 font-mono text-sm text-slate-400"></p>
      </div>
      <button type="button" id="modalClose" class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm hover:bg-white/10">Tutup</button>
    </div>
    <div class="mt-4 grid gap-3 text-sm text-slate-300 sm:grid-cols-2">
      <div><span class="text-slate-500">Divisi:</span> <span id="modalDivision"></span></div>
      <div><span class="text-slate-500">Sistem:</span> <span id="modalSystem"></span></div>
      <div><span class="text-slate-500">Vendor PIC:</span> <span id="modalVendor"></span></div>
      <div><span class="text-slate-500">Tanggal:</span> <span id="modalDate"></span></div>
    </div>
    <p id="modalReason" class="mt-4 text-sm leading-6 text-slate-300"></p>
    <div class="mt-5 flex flex-wrap gap-2">
      <a id="modalLink" href="#" class="rounded-xl bg-cyan-500 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-400">Buka Detail</a>
      <button type="button" id="modalCopy" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm hover:bg-white/10">Salin Nomor</button>
    </div>
  </div>
</div>

<script>
  window.itBoardCrConfig = {
    dashboardUrl: <?php echo json_encode(route('it.board_cr.dashboard'), 15, 512) ?>,
    updateUrlBase: <?php echo json_encode(url('/it/board-cr'), 15, 512) ?>,
    csrf: <?php echo json_encode(csrf_token(), 15, 512) ?>,
  };
</script>

<script>
(() => {
  const columns = ['open', 'vendor_development', 'uat', 'go_live', 'closed'];
  const state = new Map();
  const boardMeta = document.getElementById('boardMeta');
  const boardNotice = document.getElementById('boardNotice');
  const reloadButton = document.getElementById('reloadBoard');
  const modal = document.getElementById('crModal');
  const modalClose = document.getElementById('modalClose');
  const modalCopy = document.getElementById('modalCopy');
  const modalLink = document.getElementById('modalLink');
  let activeItem = null;

  function showNotice(message) {
    boardNotice.textContent = message;
    boardNotice.classList.remove('hidden');
  }

  function hideNotice() {
    boardNotice.classList.add('hidden');
    boardNotice.textContent = '';
  }

  function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  }

  function pick(item, keys, fallback = '-') {
    for (const key of keys) {
      const value = key.split('.').reduce((acc, part) => (acc && acc[part] !== undefined ? acc[part] : undefined), item);
      if (value !== undefined && value !== null && String(value).trim() !== '') return value;
    }
    return fallback;
  }

  function getStatusLabel(status) {
    const map = {
      open: 'Backlog',
      vendor_development: 'On Progress',
      uat: 'Testing UAT',
      go_live: 'Go Live',
      closed: 'Done',
    };
    return map[status] || status;
  }

  function renderEmptyStates() {
    columns.forEach((status) => {
      const dropzone = document.querySelector(`[data-dropzone="${status}"]`);
      if (!dropzone) return;
      const cards = dropzone.querySelectorAll('[draggable="true"]');
      let empty = dropzone.querySelector('.board-empty');
      if (cards.length === 0) {
        if (!empty) {
          empty = document.createElement('div');
          empty.className = 'board-empty rounded-xl border border-dashed border-white/10 px-3 py-4 text-center text-sm text-slate-400';
          empty.textContent = 'Belum ada CR.';
          dropzone.appendChild(empty);
        }
      } else if (empty) {
        empty.remove();
      }
      const counter = document.querySelector(`[data-count-for="${status}"]`);
      if (counter) counter.textContent = String(cards.length);
    });
  }

  function cardTemplate(item) {
    const id = pick(item, ['id']);
    const nomor = pick(item, ['nomor', 'nomor_tiket', 'number']);
    const title = pick(item, ['judul', 'title', 'name', 'deskripsi']);
    const division = pick(item, ['divisi', 'division', 'department']);
    const system = pick(item, ['sistem', 'system', 'application']);
    const date = pick(item, ['tanggal', 'created_at', 'date']);
    const status = String(pick(item, ['status.value', 'status', 'state'], 'open')).toLowerCase();
    const url = `${window.itBoardCrConfig.updateUrlBase}/${encodeURIComponent(id)}`;

    const el = document.createElement('article');
    el.className = 'cr-card cursor-grab rounded-2xl border border-white/10 bg-slate-900/90 p-3 shadow-lg shadow-black/20 transition hover:-translate-y-0.5 hover:border-cyan-300/30';
    el.draggable = true;
    el.dataset.id = id;
    el.dataset.status = status;
    el.dataset.url = url;
    el.innerHTML = `
      <div class="flex items-start justify-between gap-2">
        <div class="min-w-0">
          <h3 class="truncate text-sm font-semibold text-white">${escapeHtml(title)}</h3>
          <p class="mt-1 font-mono text-[11px] text-slate-400">${escapeHtml(nomor)}</p>
        </div>
        <span class="shrink-0 rounded-full border border-cyan-400/40 bg-cyan-400/10 px-2 py-1 text-[11px] font-semibold text-cyan-200">
          ${escapeHtml(getStatusLabel(status))}
        </span>
      </div>
      <dl class="mt-3 space-y-1 text-xs text-slate-300">
        <div><dt class="inline text-slate-500">Divisi:</dt> <dd class="inline">${escapeHtml(division)}</dd></div>
        <div><dt class="inline text-slate-500">Sistem:</dt> <dd class="inline">${escapeHtml(system)}</dd></div>
        <div><dt class="inline text-slate-500">Tanggal:</dt> <dd class="inline">${escapeHtml(date)}</dd></div>
      </dl>
      <div class="mt-4 flex justify-end">
        <button type="button" class="rounded-lg border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-white hover:bg-white/10" data-open-detail>Detail</button>
      </div>
    `;

    el.addEventListener('dragstart', (event) => {
      event.dataTransfer.effectAllowed = 'move';
      event.dataTransfer.setData('text/plain', id);
      el.classList.add('opacity-60');
      activeItem = item;
    });

    el.addEventListener('dragend', () => {
      el.classList.remove('opacity-60');
    });

    el.querySelector('[data-open-detail]')?.addEventListener('click', () => openModal(item, url));

    return el;
  }

  function openModal(item, url) {
    activeItem = item;
    const nomor = pick(item, ['nomor', 'nomor_tiket', 'number']);
    const title = pick(item, ['judul', 'title', 'name', 'deskripsi']);
    document.getElementById('modalStatus').textContent = getStatusLabel(String(pick(item, ['status.value', 'status', 'state'], 'open')).toLowerCase());
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalNumber').textContent = nomor;
    document.getElementById('modalDivision').textContent = pick(item, ['divisi', 'division', 'department']);
    document.getElementById('modalSystem').textContent = pick(item, ['sistem', 'system', 'application']);
    document.getElementById('modalVendor').textContent = pick(item, ['vendor_pic', 'vendor_pic_name', 'vendor']);
    document.getElementById('modalDate').textContent = pick(item, ['tanggal', 'created_at', 'date']);
    document.getElementById('modalReason').textContent = pick(item, ['change_reason', 'reason', 'deskripsi', 'note'], 'Tidak ada detail tambahan.');
    modalLink.href = url;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
  }

  async function loadBoard() {
    hideNotice();
    boardMeta.textContent = 'Memuat data board CR...';

    try {
      const response = await fetch(window.itBoardCrConfig.dashboardUrl, {
        headers: { 'Accept': 'application/json' }
      });

      const json = await response.json();
      if (!response.ok || json.ok === false) {
        throw new Error(json.message || 'Gagal memuat board.');
      }

      const board = json.board || {};
      const items = Array.isArray(board.columns) ? board.columns : [];
      const meta = board.meta || {};

      columns.forEach((status) => {
        const dropzone = document.querySelector(`[data-dropzone="${status}"]`);
        if (!dropzone) return;
        dropzone.querySelectorAll('[draggable="true"]').forEach((node) => node.remove());
      });

      items.forEach((column) => {
        const status = String(column.status || column.key || '').toLowerCase();
        const dropzone = document.querySelector(`[data-dropzone="${status}"]`);
        if (!dropzone) return;
        const list = Array.isArray(column.items) ? column.items : [];
        list.forEach((item) => {
          dropzone.appendChild(cardTemplate(item));
        });
      });

      renderEmptyStates();
      boardMeta.textContent = `Total CR: ${meta.total_items ?? 0} | Generated: ${meta.generated_at ?? '-'}`;
    } catch (error) {
      console.error(error);
      showNotice(error.message || 'Gagal memuat board CR.');
      boardMeta.textContent = 'Board gagal dimuat.';
    }
  }

  async function updateStatus(item, status, note) {
    const url = `${window.itBoardCrConfig.updateUrlBase}/${encodeURIComponent(item.id)}/status`;
    const response = await fetch(url, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': window.itBoardCrConfig.csrf,
      },
      body: JSON.stringify({ status, note: note || `Dipindah ke ${getStatusLabel(status)}` }),
    });

    const json = await response.json().catch(() => ({}));
    if (!response.ok || json.ok === false) {
      throw new Error(json.message || 'Gagal memperbarui status.');
    }
  }

  document.querySelectorAll('.board-dropzone').forEach((zone) => {
    zone.addEventListener('dragover', (event) => {
      event.preventDefault();
      zone.classList.add('ring-2', 'ring-cyan-400/40');
    });
    zone.addEventListener('dragleave', () => {
      zone.classList.remove('ring-2', 'ring-cyan-400/40');
    });
    zone.addEventListener('drop', async (event) => {
      event.preventDefault();
      zone.classList.remove('ring-2', 'ring-cyan-400/40');
      const itemId = event.dataTransfer.getData('text/plain');
      const card = document.querySelector(`.cr-card[data-id="${CSS.escape(itemId)}"]`);
      if (!card) return;
      const targetStatus = zone.dataset.dropzone;
      const currentStatus = String(card.dataset.status || '').toLowerCase();
      if (!targetStatus || currentStatus === targetStatus) return;

      const relatedItem = activeItem || { id: itemId };
      const previousParent = card.parentElement;
      try {
        card.classList.add('opacity-60');
        await updateStatus(relatedItem, targetStatus);
        zone.appendChild(card);
        card.dataset.status = targetStatus;
        renderEmptyStates();
      } catch (error) {
        showNotice(error.message || 'Gagal memindah CR.');
        if (previousParent) previousParent.appendChild(card);
      } finally {
        card.classList.remove('opacity-60');
      }
    });
  });

  reloadButton?.addEventListener('click', loadBoard);
  modalClose?.addEventListener('click', () => {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
  });
  modal?.addEventListener('click', (event) => {
    if (event.target === modal) {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    }
  });
  modalCopy?.addEventListener('click', async () => {
    if (!activeItem) return;
    try {
      await navigator.clipboard.writeText(String(pick(activeItem, ['nomor', 'nomor_tiket', 'number'])));
      modalCopy.textContent = 'Tersalin';
      setTimeout(() => { modalCopy.textContent = 'Salin Nomor'; }, 1200);
    } catch (_) {}
  });

  loadBoard();
})();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\helpdesk-app\resources\views/it/board_cr.blade.php ENDPATH**/ ?>