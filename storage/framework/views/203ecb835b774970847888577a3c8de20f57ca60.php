<?php $__env->startSection('title','Manajemen User'); ?>

<?php $__env->startSection('content'); ?>
<?php
  $roles = ['IT', 'CABANG', 'VENDOR', 'ADMIN'];
  $perPageOptions = [10, 25, 50, 100];
  $btnText = 'inline-flex h-9 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 shadow-sm hover:bg-violet-50 hover:text-violet-700 focus:outline-none focus:ring-2 focus:ring-violet-500';
  $filterInput = 'h-10 w-full rounded-lg border-slate-200 bg-white text-sm text-slate-700 shadow-sm placeholder:text-slate-400 focus:border-violet-500 focus:ring-violet-500';
  $modalInput = 'mt-1 h-10 w-full rounded-lg border-slate-200 bg-white text-sm text-slate-800 shadow-sm focus:border-violet-500 focus:ring-violet-500';
  $modalLabel = 'text-xs font-semibold uppercase tracking-wide text-slate-500';
  $firstItem = $users->firstItem() ?? 0;
  $lastItem = $users->lastItem() ?? 0;
?>

<div
  class="w-full max-w-none pb-4 text-[13px]"
  x-data="{
    createModalOpen: false,
    editModalOpen: false,
    createUser: { username: '', name: '', email: '', password: '', role: 'IT', kode_kantor: '' },
    editUser: {},
    openCreate() {
      this.createUser = { username: '', name: '', email: '', password: '', role: 'IT', kode_kantor: '' };
      this.createModalOpen = true;
      this.$nextTick(() => this.$refs.createName?.focus());
    },
    closeCreate() {
      this.createModalOpen = false;
      this.createUser = { username: '', name: '', email: '', password: '', role: 'IT', kode_kantor: '' };
    },
    openEdit(user) {
      this.editUser = { ...user, password: '' };
      this.editModalOpen = true;
      this.$nextTick(() => this.$refs.editName?.focus());
    },
    closeEdit() {
      this.editModalOpen = false;
      this.editUser = {};
    }
  }"
  @keydown.escape.window="createModalOpen ? closeCreate() : (editModalOpen && closeEdit())"
>
  <div class="space-y-3">
    <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex min-w-0 items-center gap-2 text-base font-semibold text-slate-900">
        <a href="<?php echo e(route('dashboard')); ?>" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-violet-500 hover:bg-violet-50" title="Dashboard">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M11.47 3.84a.75.75 0 0 1 1.06 0l8.69 8.69a.75.75 0 1 1-1.06 1.06l-.66-.66V20a1.5 1.5 0 0 1-1.5 1.5h-4.25a.75.75 0 0 1-.75-.75V16a1 1 0 0 0-2 0v4.75a.75.75 0 0 1-.75.75H6a1.5 1.5 0 0 1-1.5-1.5v-7.07l-.66.66a.75.75 0 0 1-1.06-1.06l8.69-8.69Z"/></svg>
        </a>
        <span class="text-slate-300">/</span>
        <span class="truncate">Manajemen User</span>
      </div>

      <button type="button" class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-violet-500 px-4 text-sm font-semibold text-white shadow-sm shadow-violet-500/25 hover:bg-violet-600 focus:outline-none focus:ring-2 focus:ring-violet-500" @click="openCreate()">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke-width="2" stroke-linecap="round"/></svg>
        Tambah User
      </button>
    </section>

    <?php if(session('success')): ?>
      <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm" role="status"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
      <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-sm" role="alert"><?php echo e(session('error')); ?></div>
    <?php endif; ?>
    <?php if($errors->any()): ?>
      <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-sm" role="alert"><?php echo e($errors->first()); ?></div>
    <?php endif; ?>

    <section class="flex max-h-[calc(100vh-6.75rem)] min-h-[26rem] flex-col overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
      <div class="shrink-0 px-4 py-3">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
          <form id="userFilterForm" method="GET" action="<?php echo e(route('it.users.index')); ?>" class="grid grid-cols-1 gap-3 md:grid-cols-[16rem_14rem_16rem_auto] xl:max-w-4xl">
            <input type="hidden" name="per_page" value="<?php echo e($perPage); ?>">
            <div class="relative">
              <input type="text" name="q" value="<?php echo e($q); ?>" placeholder="Cari disini..." class="<?php echo e($filterInput); ?> pr-10">
              <button type="submit" class="absolute inset-y-0 right-0 inline-flex w-10 items-center justify-center text-slate-400 hover:text-violet-600" title="Cari">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><circle cx="11" cy="11" r="7" stroke-width="2"/><path d="m20 20-3.5-3.5" stroke-width="2" stroke-linecap="round"/></svg>
              </button>
            </div>
            <select name="role" class="<?php echo e($filterInput); ?>" onchange="this.form.submit()">
              <option value="">Filter Role</option>
              <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($item); ?>" <?php if($role === $item): echo 'selected'; endif; ?>><?php echo e($item); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <select name="kode_kantor" class="<?php echo e($filterInput); ?>" onchange="this.form.submit()">
              <option value="">Filter Cabang</option>
              <?php $__currentLoopData = $kodeKantors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $office): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($office->kode); ?>" <?php if(($kodeKantor ?? '') === $office->kode): echo 'selected'; endif; ?>><?php echo e($office->kode); ?> - <?php echo e($office->nama_kantor); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <a href="<?php echo e(route('it.users.index')); ?>" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-violet-500 hover:bg-violet-50" title="Reset filter">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M21 12a9 9 0 1 1-2.64-6.36" stroke-width="2" stroke-linecap="round"/><path d="M21 3v6h-6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
          </form>

          <div class="flex flex-wrap items-center gap-2 xl:justify-end">
            <a href="<?php echo e(route('it.users.export', request()->query())); ?>" class="<?php echo e($btnText); ?>" title="Export Excel">
              Export
            </a>
            <form method="POST" action="<?php echo e(route('it.users.import')); ?>" enctype="multipart/form-data">
              <?php echo csrf_field(); ?>
              <label class="<?php echo e($btnText); ?> cursor-pointer" title="Import Excel">
                Import
                <input type="file" name="file" accept=".xlsx,.xls" required class="sr-only" onchange="this.form.submit()">
              </label>
            </form>
          </div>
        </div>
      </div>

      <div class="min-h-0 flex-1 overflow-auto px-4">
        <table class="min-w-full border-collapse text-sm">
          <thead class="sticky top-0 z-10">
            <tr class="bg-violet-50 text-slate-900">
              <th class="border-b border-violet-100 px-3 py-2.5 text-left font-semibold">Nama</th>
              <th class="border-b border-violet-100 px-3 py-2.5 text-left font-semibold">Username</th>
              <th class="border-b border-violet-100 px-3 py-2.5 text-left font-semibold">Email</th>
              <th class="border-b border-violet-100 px-3 py-2.5 text-left font-semibold">Cabang</th>
              <th class="border-b border-violet-100 px-3 py-2.5 text-left font-semibold">Role</th>
              <th class="border-b border-violet-100 px-3 py-2.5 text-left font-semibold">AI</th>
              <th class="border-b border-violet-100 px-3 py-2.5 text-left font-semibold">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 bg-white">
            <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <tr class="text-slate-700 hover:bg-slate-50">
                <td class="px-3 py-2.5 font-medium text-slate-800"><?php echo e($user->name); ?></td>
                <td class="px-3 py-2.5"><?php echo e($user->username); ?></td>
                <td class="px-3 py-2.5 break-all"><?php echo e($user->email); ?></td>
                <td class="px-3 py-2.5">
                  <?php if($user->kodeKantor): ?>
                    <?php echo e($user->kodeKantor->nama_kantor); ?>

                  <?php else: ?>
                    <span class="text-slate-400">-</span>
                  <?php endif; ?>
                </td>
                <td class="px-3 py-2.5"><?php echo e($user->role); ?></td>
                <td class="px-3 py-2.5">
                  <form method="POST" action="<?php echo e(route('it.users.ai-chat', array_merge(request()->query(), ['user' => $user]))); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PATCH'); ?>
                    <input type="hidden" name="ai_chat_enabled" value="0">
                    <label class="inline-flex cursor-pointer items-center gap-2">
                      <input type="checkbox" name="ai_chat_enabled" value="1" class="sr-only peer" <?php if($user->ai_chat_enabled): echo 'checked'; endif; ?> onchange="this.form.submit()">
                      <span class="relative inline-flex h-6 w-11 items-center rounded-full bg-slate-300 transition peer-checked:bg-violet-500">
                        <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                          'ml-1 h-4 w-4 rounded-full bg-white shadow transition',
                          'translate-x-5' => $user->ai_chat_enabled,
                        ]) ?>"></span>
                      </span>
                      <span class="min-w-[5.5rem] text-sm" class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'text-violet-700 font-semibold' => $user->ai_chat_enabled,
                        'text-slate-500' => ! $user->ai_chat_enabled,
                      ]) ?>">
                        <?php echo e($user->ai_chat_enabled ? 'AI Enable' : 'AI Disable'); ?>

                      </span>
                    </label>
                  </form>
                </td>
                <td class="px-3 py-2.5">
                  <div class="flex items-center gap-2">
                    <?php
                      $editUserPayload = base64_encode(json_encode([
                        'id' => $user->id,
                        'update_url' => route('it.users.update', $user),
                        'username' => $user->username,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'kode_kantor' => $user->kode_kantor,
                      ]));
                    ?>
                    <button
                      type="button"
                      class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500 text-white hover:bg-sky-600"
                      title="Edit"
                      data-edit-user="<?php echo e($editUserPayload); ?>"
                      @click="openEdit(JSON.parse(atob($el.dataset.editUser)))"
                    >
                      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M4 20h4l10.5-10.5a2.12 2.12 0 0 0-3-3L5 17v3Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                    <?php if(auth()->id() === $user->id): ?>
                      <button type="button" disabled class="inline-flex h-8 w-8 cursor-not-allowed items-center justify-center rounded-lg bg-slate-200 text-white" title="Tidak dapat menghapus akun sendiri">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 16h10l1-16M9 7V4h6v3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                      </button>
                    <?php else: ?>
                      <form method="POST" action="<?php echo e(route('it.users.destroy', $user)); ?>" onsubmit="return confirm('Hapus user ini?')">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-red-500 text-white hover:bg-red-600" title="Delete">
                          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 16h10l1-16M9 7V4h6v3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                      </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <tr>
                <td colspan="7" class="px-3 py-10 text-center text-sm text-slate-500">Belum ada user sesuai filter.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <div class="shrink-0 border-t border-slate-100 px-4 py-3">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
          <form method="GET" action="<?php echo e(route('it.users.index')); ?>" class="flex items-center gap-2">
            <input type="hidden" name="q" value="<?php echo e($q); ?>">
            <input type="hidden" name="role" value="<?php echo e($role); ?>">
            <input type="hidden" name="kode_kantor" value="<?php echo e($kodeKantor); ?>">
            <label for="per_page" class="text-sm text-slate-700">Rows per page</label>
            <select id="per_page" name="per_page" class="h-10 rounded-lg border-slate-200 bg-white px-3 text-sm text-slate-700 shadow-sm focus:border-violet-500 focus:ring-violet-500" onchange="this.form.submit()">
              <?php $__currentLoopData = $perPageOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($option); ?>" <?php if($perPage === $option): echo 'selected'; endif; ?>><?php echo e($option); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
          </form>
          <div class="text-sm text-slate-700"><?php echo e($firstItem); ?>-<?php echo e($lastItem); ?> of <?php echo e($users->total()); ?></div>
          <div class="flex items-center gap-2">
            <?php if($users->previousPageUrl()): ?>
              <a href="<?php echo e($users->previousPageUrl()); ?>" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200" title="Previous">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="m15 18-6-6 6-6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </a>
            <?php else: ?>
              <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-300">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="m15 18-6-6 6-6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </span>
            <?php endif; ?>

            <?php if($users->nextPageUrl()): ?>
              <a href="<?php echo e($users->nextPageUrl()); ?>" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200" title="Next">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="m9 18 6-6-6-6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </a>
            <?php else: ?>
              <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-300">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="m9 18 6-6-6-6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </section>
  </div>

  <div
    x-cloak
    x-show="createModalOpen"
    x-transition.opacity
    class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/45 p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="create-user-title"
  >
    <div class="absolute inset-0" @click="closeCreate()"></div>
    <section
      x-show="createModalOpen"
      x-transition.scale.origin.center
      class="relative w-full max-w-3xl overflow-hidden rounded-lg bg-white shadow-2xl"
    >
      <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
        <div>
          <h2 id="create-user-title" class="text-base font-semibold text-slate-900">Tambah User</h2>
          <p class="mt-1 text-sm text-slate-500">Buat akun baru dan tentukan role serta kantor terkait.</p>
        </div>
        <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700" @click="closeCreate()" title="Tutup">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" stroke-width="2" stroke-linecap="round"/></svg>
        </button>
      </div>

      <form method="POST" action="<?php echo e(route('it.users.store')); ?>" class="px-5 py-4">
        <?php echo csrf_field(); ?>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div>
            <label for="create_username" class="<?php echo e($modalLabel); ?>">Username</label>
            <input id="create_username" type="text" name="username" x-model="createUser.username" class="<?php echo e($modalInput); ?>" required>
          </div>
          <div>
            <label for="create_name" class="<?php echo e($modalLabel); ?>">Nama</label>
            <input id="create_name" x-ref="createName" type="text" name="name" x-model="createUser.name" class="<?php echo e($modalInput); ?>" required>
          </div>
          <div>
            <label for="create_email" class="<?php echo e($modalLabel); ?>">Email</label>
            <input id="create_email" type="email" name="email" x-model="createUser.email" class="<?php echo e($modalInput); ?>" required>
          </div>
          <div>
            <label for="create_password" class="<?php echo e($modalLabel); ?>">Password</label>
            <input id="create_password" type="password" name="password" x-model="createUser.password" class="<?php echo e($modalInput); ?>" required>
          </div>
          <div>
            <label for="create_role" class="<?php echo e($modalLabel); ?>">Role</label>
            <select id="create_role" name="role" x-model="createUser.role" class="<?php echo e($modalInput); ?>" required>
              <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($item); ?>"><?php echo e($item); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
          </div>
          <div>
            <label for="create_kode_kantor" class="<?php echo e($modalLabel); ?>">Kode kantor</label>
            <select id="create_kode_kantor" name="kode_kantor" x-model="createUser.kode_kantor" class="<?php echo e($modalInput); ?>">
              <option value="">Tidak dipilih</option>
              <?php $__currentLoopData = $kodeKantors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $office): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($office->kode); ?>"><?php echo e($office->kode); ?> - <?php echo e($office->nama_kantor); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
          </div>
        </div>

        <div class="mt-5 flex flex-wrap justify-end gap-2 border-t border-slate-100 pt-4">
          <button type="button" class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50" @click="closeCreate()">Batal</button>
          <button type="submit" class="inline-flex h-10 items-center justify-center rounded-lg bg-violet-500 px-4 text-sm font-semibold text-white shadow-sm shadow-violet-500/25 hover:bg-violet-600">Simpan User</button>
        </div>
      </form>
    </section>
  </div>

  <div
    x-cloak
    x-show="editModalOpen"
    x-transition.opacity
    class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/45 p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="edit-user-title"
  >
    <div class="absolute inset-0" @click="closeEdit()"></div>
    <section
      x-show="editModalOpen"
      x-transition.scale.origin.center
      class="relative w-full max-w-3xl overflow-hidden rounded-lg bg-white shadow-2xl"
    >
      <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
        <div>
          <h2 id="edit-user-title" class="text-base font-semibold text-slate-900">Edit User</h2>
          <p class="mt-1 text-sm text-slate-500" x-text="editUser.email || '-'"></p>
        </div>
        <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700" @click="closeEdit()" title="Tutup">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" stroke-width="2" stroke-linecap="round"/></svg>
        </button>
      </div>

      <form method="POST" :action="editUser.update_url" class="px-5 py-4">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <input type="hidden" name="edit_user_id" :value="editUser.id">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div>
            <label for="edit_username" class="<?php echo e($modalLabel); ?>">Username</label>
            <input id="edit_username" type="text" name="username" x-model="editUser.username" class="<?php echo e($modalInput); ?>" required>
          </div>
          <div>
            <label for="edit_name" class="<?php echo e($modalLabel); ?>">Nama</label>
            <input id="edit_name" x-ref="editName" type="text" name="name" x-model="editUser.name" class="<?php echo e($modalInput); ?>" required>
          </div>
          <div>
            <label for="edit_email" class="<?php echo e($modalLabel); ?>">Email</label>
            <input id="edit_email" type="email" name="email" x-model="editUser.email" class="<?php echo e($modalInput); ?>" required>
          </div>
          <div>
            <label for="edit_role" class="<?php echo e($modalLabel); ?>">Role</label>
            <select id="edit_role" name="role" x-model="editUser.role" class="<?php echo e($modalInput); ?>" required>
              <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($item); ?>"><?php echo e($item); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
          </div>
          <div>
            <label for="edit_kode_kantor" class="<?php echo e($modalLabel); ?>">Kode kantor</label>
            <select id="edit_kode_kantor" name="kode_kantor" x-model="editUser.kode_kantor" class="<?php echo e($modalInput); ?>">
              <option value="">Tidak dipilih</option>
              <?php $__currentLoopData = $kodeKantors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $office): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($office->kode); ?>"><?php echo e($office->kode); ?> - <?php echo e($office->nama_kantor); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
          </div>
          <div>
            <label for="edit_password" class="<?php echo e($modalLabel); ?>">Password baru</label>
            <input id="edit_password" type="password" name="password" x-model="editUser.password" class="<?php echo e($modalInput); ?>" placeholder="Kosongkan jika tidak diganti">
          </div>
        </div>

        <div class="mt-5 flex flex-wrap justify-end gap-2 border-t border-slate-100 pt-4">
          <button type="button" class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50" @click="closeEdit()">Batal</button>
          <button type="submit" class="inline-flex h-10 items-center justify-center rounded-lg bg-violet-500 px-4 text-sm font-semibold text-white shadow-sm shadow-violet-500/25 hover:bg-violet-600">Simpan Perubahan</button>
        </div>
      </form>
    </section>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\helpdesk-app\resources\views/it/users/index.blade.php ENDPATH**/ ?>