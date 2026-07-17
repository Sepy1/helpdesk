<!doctype html>
<html lang="id" class="h-full bg-gray-100">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
  <meta name="theme-color" content="#3B82F6">
  <title><?php echo $__env->yieldContent('title','Helpdesk'); ?></title>
 <style>[x-cloak]{display:none!important}</style>
  <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css','resources/js/app.js']); ?>

  
  <style>[x-cloak]{display:none!important}</style>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

  
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nprogress@0.2.0/nprogress.css">
  <script defer src="https://cdn.jsdelivr.net/npm/nprogress@0.2.0/nprogress.js"></script>

  <style>
    :root{
      --topbar-h: 4rem;
      --sidebar-w: 16rem;
      --content-pt: 1.5rem;
    }

    .spin{animation:spin 1s linear infinite}@keyframes spin{to{transform:rotate(360deg)}}
    #nprogress .bar{background:#6366F1!important;height:3px!important}
    #nprogress .peg{box-shadow:0 0 10px #6366F1,0 0 5px #6366F1!important}

    /* Konten tetap stabil untuk mencegah flicker */
    .page-root{opacity:1;transform:none;transition:none;will-change:auto}
    html.loading .page-root{opacity:1;transform:none}

    /* overlay loader */
    #page-loader{display:flex;opacity:0;pointer-events:none;transition:opacity .16s ease}
    html.loading #page-loader{opacity:1;pointer-events:auto}

    .layout-sidebar{
      top: 0;
    }
    .layout-sidebar .sidebar-shell{
      padding-top: var(--topbar-h);
    }

    /* sidebar fade (opsional) */
    aside[aria-label="Sidebar"]>div{transition:opacity .25s ease}

    @media (prefers-reduced-motion:reduce){
      .page-root,#page-loader,aside[aria-label="Sidebar"]>div{transition:none!important}
    }

    /* Desktop AI assistant */
    .ai-fab{box-shadow:0 12px 26px rgba(99,102,241,.35)}
    .ai-panel{box-shadow:0 18px 40px rgba(15,23,42,.22)}

    /* Desktop scale-down for selected pages */
    @media (min-width: 1024px){
      .desktop-scale-80{
        zoom: 80%;
      }
      @supports not (zoom: 1){
        .desktop-scale-80{
          transform: scale(.8);
          transform-origin: top left;
          width: 125%;
        }
      }
      .ui-compact-80{
        --topbar-h: 3.2rem;   /* 80% of 4rem */
        --sidebar-w: 12.8rem; /* 80% of 16rem */
        --content-pt: 1.2rem; /* 80% of 1.5rem */
      }
      .ui-compact-80 .topbar-inner{
        height: var(--topbar-h);
      }
      .ui-compact-80 .layout-sidebar{
        width: var(--sidebar-w);
      }
      .ui-compact-80 .layout-content{
        padding-top: var(--content-pt);
      }
      .ui-compact-80 .layout-content.sidebar-open{
        margin-left: var(--sidebar-w);
      }
      .ui-compact-80 .layout-content.sidebar-closed{
        margin-left: 0;
      }

      /* Shrink topbar internals (icons, text, controls) */
      .ui-compact-80 .topbar-inner .h-9{ height: 1.8rem; }
      .ui-compact-80 .topbar-inner .w-9{ width: 1.8rem; }
      .ui-compact-80 .topbar-inner .h-5{ height: 1rem; }
      .ui-compact-80 .topbar-inner .w-5{ width: 1rem; }
      .ui-compact-80 .topbar-inner .topbar-logo{ height: 1.8rem; width: 1.8rem; }
      .ui-compact-80 .topbar-inner .topbar-art{ height: 1.8rem; max-width: 6.4rem; }
      .ui-compact-80 .topbar-inner .topbar-title{ font-size: .85rem; line-height: 1rem; }
      .ui-compact-80 .topbar-inner .topbar-user{ font-size: .7rem; line-height: 1rem; }
      .ui-compact-80 .topbar-inner .topbar-action{ font-size: .7rem; padding: .25rem .6rem; }

      /* Shrink sidebar internals (font, icon, spacing) */
      .ui-compact-80 .layout-sidebar .sidebar-shell{
        padding: .8rem;
        padding-top: var(--topbar-h);
      }
      .ui-compact-80 .layout-sidebar .text-sm{ font-size: .7rem; line-height: 1rem; }
      .ui-compact-80 .layout-sidebar .text-xs{ font-size: .6rem; line-height: .9rem; }
      .ui-compact-80 .layout-sidebar nav a{ padding: .5rem .6rem; gap: .55rem; border-radius: .5rem; }
      .ui-compact-80 .layout-sidebar nav a svg{ width: 1rem; height: 1rem; }
      .ui-compact-80 .layout-sidebar .h-8{ height: 1.6rem; }
      .ui-compact-80 .layout-sidebar .w-8{ width: 1.6rem; }
    }
  </style>

  
  <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<?php
  $logoPath = asset('images/helpdesk.png');
  $topbarArtPath = asset('images/topbar.png');
  $isDesktopScaledRoute = request()->routeIs([
    'ticket.show',
    'it.ticket.create',
    'cabang.dashboard',
    'it.dashboard',
    'it.my',
    'cabang.tickets',
    'vendor.tickets',
    'it.stats',
    'it.parameters',
    'it.users.*',
    'profile.edit',
    'vendor.profile.edit'
  ]);
?>

<?php
  $aiChatEnabled = true;
  $aiChatEnabledForUser = true;
  try {
    $aiChatEnabled = \App\Models\AppSetting::getBool('ai_chat_enabled', true);
    if (auth()->check() && auth()->user()->ai_chat_enabled === false) {
      $aiChatEnabledForUser = false;
    }
  } catch (\Throwable $e) {
    $aiChatEnabled = true;
    $aiChatEnabledForUser = true;
  }
?>

<body
  class="h-full font-sans antialiased bg-gray-100 <?php echo e($isDesktopScaledRoute ? 'ui-compact-80' : ''); ?>"
  x-data="layoutState()"
  x-init="init()"
  :class="{ 'overflow-hidden': mobileOpen }"
>

  
<header class="bg-gradient-to-r from-blue-700 via-indigo-600 to-violet-600 sticky top-0 z-40 h-[var(--topbar-h)]">
  <div class="topbar-inner relative z-[1] w-full h-full flex items-center justify-between">
    <div class="topbar-brand flex items-center gap-2 min-w-0 h-full pl-4 pr-2 md:w-[var(--sidebar-w)] md:shrink-0 md:pr-3">
      <img src="<?php echo e($logoPath); ?>" alt="Logo Helpdesk" class="topbar-logo h-9 w-9 object-contain shrink-0 drop-shadow-[0_0_8px_rgba(255,255,255,0.35)]" onerror="this.style.display='none'">
      <span class="topbar-title flex-1 min-w-0 font-semibold text-white truncate">Sambatan</span>

      <button type="button"
              class="hidden md:inline-flex shrink-0 h-9 w-9 items-center justify-center rounded-lg hover:bg-white/10 text-white"
              @click="toggleSidebar()" :aria-pressed="sidebarOpen.toString()"
              aria-label="Tampilkan/sembunyikan menu">☰</button>

      <button type="button"
              class="inline-flex md:hidden shrink-0 h-9 w-9 items-center justify-center rounded-lg hover:bg-white/10 text-white"
              @click="mobileOpen = true" aria-controls="mobile-drawer" aria-expanded="true" aria-label="Buka menu">☰</button>
    </div>

    <?php if(auth()->guard()->check()): ?>
    <img
      src="<?php echo e($topbarArtPath); ?>"
      alt=""
      class="topbar-art hidden md:block h-9 w-auto max-w-[8rem] object-contain shrink-0 drop-shadow-[0_0_6px_rgba(255,255,255,0.25)] ml-2 md:ml-3"
      loading="lazy"
      decoding="async"
      aria-hidden="true"
      onerror="this.style.display='none'"
    >
    <?php endif; ?>

    <nav class="flex items-center gap-3 text-sm min-w-0 ml-auto pr-4 sm:pr-6 lg:pr-8">
      <?php if(auth()->guard()->check()): ?>
        <div x-data="notifState()" x-init="init()" class="relative">
          <button @click="toggle()" @keydown.escape.window="open=false" type="button"
                  class="relative h-9 w-9 inline-flex items-center justify-center rounded-lg hover:bg-white/10 text-white"
                  aria-label="Notifikasi">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
              <path d="M12 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 006 14h12a1 1 0 00.707-1.707L18 11.586V8a6 6 0 00-6-6zm0 20a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
            </svg>
            <span x-show="count>0" x-cloak class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 inline-flex items-center justify-center rounded-full bg-red-600 text-white text-[10px] font-bold" x-text="badgeText()"></span>
          </button>
          <button
            type="button"
            @click="toggleDesktopNotifications()"
            class="ml-2 hidden xl:inline-flex h-9 items-center rounded-lg bg-white/10 px-3 text-xs font-medium text-white hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/40 disabled:cursor-default disabled:opacity-60"
            :title="browserNotifEnabled() ? 'Matikan notifikasi desktop' : 'Aktifkan notifikasi desktop browser'">
            <span x-show="browserNotifEnabled()" x-cloak>Notif Aktif</span>
            <span x-show="!browserNotifEnabled()" x-cloak>Aktifkan Notif Desktop</span>
          </button>
          <div x-show="open" x-cloak @click.outside="open=false" @mouseenter="cancelAutoClose()" @mouseleave="scheduleAutoClose()"
               class="fixed inset-x-2 top-[var(--topbar-h)] md:absolute md:inset-auto md:right-0 md:w-80 md:top-auto w-auto bg-white rounded-xl shadow-lg ring-1 ring-gray-200 z-50">
            <div class="flex items-center justify-between px-3 py-2 border-b">
              <div class="font-medium text-gray-800">Notifikasi</div>
              <button @click="markAll()" class="text-xs text-indigo-600 hover:underline">Tandai sudah dibaca</button>
            </div>
            <div class="max-h-[70vh] md:max-h-80 overflow-auto">
              <template x-if="items.length===0">
                <div class="px-3 py-4 text-sm text-gray-500">Tidak ada notifikasi.</div>
              </template>
              <template x-for="n in items" :key="n.id">
                <a :href="n.url || '#'"
                   class="block px-3 py-2 hover:bg-gray-50 break-words whitespace-normal"
                   :class="(n.read_at ? 'opacity-60' : 'bg-yellow-50 ring-1 ring-yellow-200')"
                   @click.prevent="open=false; markOne(n);">
                  <div class="text-sm font-medium text-gray-800" x-text="n.title"></div>
                  <div class="text-xs text-gray-600 mt-0.5" x-text="n.body"></div>
                  <div class="text-[11px] text-gray-400 mt-0.5" x-text="formatTime(n.created_at)"></div>
                </a>
              </template>
            </div>
          </div>
        </div>
        <div x-data="commentNotifState()" x-init="init()" class="relative">
          <button @click="toggle()" @keydown.escape.window="open=false" type="button"
                  class="relative h-9 w-9 inline-flex items-center justify-center rounded-lg hover:bg-white/10 text-white"
                  aria-label="Komentar">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
              <path d="M4 4h16a2 2 0 012 2v10a2 2 0 01-2 2H8l-4 4V6a2 2 0 012-2z"/>
            </svg>
            <span x-show="count>0" x-cloak class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 inline-flex items-center justify-center rounded-full bg-red-600 text-white text-[10px] font-bold" x-text="badgeText()"></span>
          </button>
          <div x-show="open" x-cloak @click.outside="open=false"
               class="fixed inset-x-2 top-[var(--topbar-h)] md:absolute md:inset-auto md:right-0 md:w-80 md:top-auto w-auto bg-white rounded-xl shadow-lg ring-1 ring-gray-200 z-50">
            <div class="flex items-center justify-between px-3 py-2 border-b">
              <div class="font-medium text-gray-800">Komentar</div>
              <button @click="markAll()" class="text-xs text-indigo-600 hover:underline">Tandai sudah dibaca</button>
            </div>
            <div class="max-h-[70vh] md:max-h-80 overflow-auto">
              <template x-if="items.length===0">
                <div class="px-3 py-4 text-sm text-gray-500">Tidak ada komentar.</div>
              </template>
              <template x-for="n in items" :key="n.id">
                <a :href="n.url || '#'"
                   class="block px-3 py-2 hover:bg-gray-50 break-words whitespace-normal"
                   :class="(n.read_at ? 'opacity-60' : 'bg-yellow-50 ring-1 ring-yellow-200')"
                   @click.prevent="open=false; markOne(n);">
                  <div class="text-sm font-medium text-gray-800" x-text="n.title"></div>
                  <div class="text-xs text-gray-600 mt-0.5" x-text="n.body"></div>
                  <div class="text-[11px] text-gray-400 mt-0.5" x-text="formatTime(n.created_at)"></div>
                </a>
              </template>
            </div>
          </div>
        </div>
        <span class="topbar-user hidden sm:inline text-white/80 truncate max-w-[40ch]">
          <?php echo e(auth()->user()->name); ?> — <span class="uppercase"><?php echo e(auth()->user()->role); ?></span>
        </span>

        
        <div class="hidden sm:inline-block mr-2">
            <?php if(auth()->user()->role === 'VENDOR'): ?>
                <a href="<?php echo e(route('vendor.profile.edit')); ?>" class="topbar-action inline-flex items-center px-3 py-1.5 rounded-lg bg-white/10 text-white hover:bg-white/20">Profil</a>
            <?php else: ?>
                <a href="<?php echo e(route('profile.edit')); ?>" class="topbar-action inline-flex items-center px-3 py-1.5 rounded-lg bg-white/10 text-white hover:bg-white/20">Profil</a>
            <?php endif; ?>
        </div>

        <form id="logout-form" method="POST" action="<?php echo e(route('logout')); ?>" class="shrink-0">
    <?php echo csrf_field(); ?>

    <button type="button"
        onclick="logoutMobile()"
        class="topbar-action inline-flex items-center px-3 py-1.5 rounded-lg bg-white/15 text-white hover:bg-white/25 focus:outline-none focus:ring-2 focus:ring-white/50">
        Logout
    </button>
</form>

<script>
function logoutMobile() {
    if (typeof AndroidApp !== "undefined") {
        // Jika di Android WebView
        AndroidApp.logoutFromApp();
    } else {
        // Jika di browser biasa
        document.getElementById('logout-form').submit();
    }
}
</script>
      <?php endif; ?>
    </nav>
  </div>
</header>


  
  <div class="relative">
    
    <aside aria-label="Sidebar"
           class="layout-sidebar hd-sidebar hidden md:block fixed z-30 bottom-0 left-0 w-64 border-r overflow-hidden
                  transition-transform duration-200 will-change-transform"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
      <div class="sidebar-shell hd-sidebar-shell h-full p-4">
        <?php echo $__env->make('layouts.partials.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      </div>
    </aside>

    
    <div class="layout-content pt-6 px-4 sm:px-6 lg:px-8 transition-[margin] duration-200
                pb-[calc(env(safe-area-inset-bottom)+72px)] md:pb-0"
         :class="sidebarOpen ? 'sidebar-open md:ml-64' : 'sidebar-closed md:ml-0'">
      

      <div id="page-root" class="page-root <?php echo e($isDesktopScaledRoute ? 'desktop-scale-80' : ''); ?>">
        <?php echo $__env->yieldContent('content'); ?>
      </div>
    </div>
  </div>

  
  <div id="mobile-drawer" x-show="mobileOpen" x-cloak
       class="fixed inset-0 z-50 md:hidden" role="dialog" aria-modal="true" aria-label="Menu navigasi">
    <div class="absolute inset-0 bg-black/40" @click="mobileOpen=false" aria-hidden="true"></div>
    <div class="absolute left-0 top-0 bottom-0 w-72 hd-sidebar border-r p-4 shadow-2xl shadow-indigo-700/35 focus:outline-none">
      <div class="mb-4 flex justify-between items-center hd-sidebar-shell">
        <div class="font-semibold text-white">Menu</div>
        <button type="button" class="h-8 w-8 inline-flex items-center justify-center rounded-md text-slate-300 hover:text-white hover:bg-white/10 transition-colors"
                @click="mobileOpen=false" aria-label="Tutup menu">✕</button>
      </div>
      <?php echo $__env->make('layouts.partials.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
  </div>

  
  <div id="page-loader"
       class="fixed inset-0 z-[9999] items-center justify-center bg-white/55"
       aria-live="polite" aria-busy="true">
    <div class="flex items-center gap-2 rounded-full bg-white/90 px-4 py-2 shadow-sm ring-1 ring-gray-200">
      <svg class="h-5 w-5 text-hd-500 spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
      </svg>
      <span class="text-xs font-medium text-gray-700">Memuat...</span>
    </div>
  </div>

  
  <?php echo $__env->make('layouts.partials.bottomnav', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

  <script>
    function browserNotifSupport(){
      return typeof window !== 'undefined' && 'Notification' in window;
    }

    function browserNotifPermission(){
      return browserNotifSupport() ? window.Notification.permission : 'unsupported';
    }

    function browserNotifEnabledKey(){
      return 'desktop-notif:enabled';
    }

    function browserNotifEnabled(){
      try{
        const raw = localStorage.getItem(browserNotifEnabledKey());
        if (raw === null) return true;
        return raw === '1';
      }catch(_){
        return true;
      }
    }

    function setBrowserNotifEnabled(enabled){
      try{
        localStorage.setItem(browserNotifEnabledKey(), enabled ? '1' : '0');
      }catch(_){}
    }

    function browserNotifStorageKey(kind){
      return kind === 'comment' ? 'desktop-notif:last-comment-id' : 'desktop-notif:last-notification-id';
    }

    function browserNotifSeenIdsKey(kind){
      return kind === 'comment' ? 'desktop-notif:seen-comment-ids' : 'desktop-notif:seen-notification-ids';
    }

    function readJsonArray(key){
      try{
        const raw = localStorage.getItem(key);
        const parsed = raw ? JSON.parse(raw) : [];
        return Array.isArray(parsed) ? parsed : [];
      }catch(_){
        return [];
      }
    }

    function writeJsonArray(key, value){
      try{
        localStorage.setItem(key, JSON.stringify(Array.isArray(value) ? value : []));
      }catch(_){}
    }

    function notifyBrowser(kind, item){
      if (!browserNotifSupport() || browserNotifPermission() !== 'granted' || !browserNotifEnabled()) return;
      const title = item?.title || (kind === 'comment' ? 'Komentar baru' : 'Aktivitas tiket');
      const body = [item?.ticket_no ? `#${item.ticket_no}` : null, item?.body || null].filter(Boolean).join(' - ');
      const icon = '<?php echo e(asset('images/logo.png')); ?>';
      const n = new window.Notification(title, {
        body: body || title,
        icon,
        tag: `${kind}:${item?.id || item?.ticket_id || title}`,
        renotify: true,
      });

      n.onclick = () => {
        try{ window.focus(); }catch(_){}
        if (item?.url) {
          window.location.href = item.url;
        }
        try{ n.close(); }catch(_){}
      };
    }

    function playNotificationSound(){
      try{
        const AudioContextClass = window.AudioContext || window.webkitAudioContext;
        if (!AudioContextClass) return;
        if (!window.__helpdeskNotifAudioCtx) {
          window.__helpdeskNotifAudioCtx = new AudioContextClass();
        }
        const ctx = window.__helpdeskNotifAudioCtx;
        if (ctx.state === 'suspended') {
          ctx.resume().catch(()=>{});
        }

        const now = ctx.currentTime;
        const makeTone = (freq, start, duration, gainValue) => {
          const osc = ctx.createOscillator();
          const gain = ctx.createGain();
          osc.type = 'sine';
          osc.frequency.setValueAtTime(freq, start);
          gain.gain.setValueAtTime(0.0001, start);
          gain.gain.exponentialRampToValueAtTime(gainValue, start + 0.02);
          gain.gain.exponentialRampToValueAtTime(0.0001, start + duration);
          osc.connect(gain);
          gain.connect(ctx.destination);
          osc.start(start);
          osc.stop(start + duration + 0.02);
        };

        makeTone(880, now, 0.12, 0.08);
        makeTone(1320, now + 0.14, 0.14, 0.06);
      }catch(_){}
    }

    async function requestBrowserNotifPermission(){
      if (!browserNotifSupport() || browserNotifPermission() !== 'default') return browserNotifPermission();
      try{
        return await window.Notification.requestPermission();
      }catch(_){
        return browserNotifPermission();
      }
    }

    function notifyFromResponse(kind, payload, state){
      const items = Array.isArray(payload?.items) ? payload.items : [];
      const unread = Number(payload?.unread || 0);
      const seenKey = browserNotifSeenIdsKey(kind);
      const seenIds = new Set(readJsonArray(seenKey).map(String));
      const sessionStartedAt = Number(state._sessionStartedAt || Date.now());

      state.count = unread;
      state.items = items;

      if (!browserNotifSupport() || browserNotifPermission() !== 'granted' || !browserNotifEnabled()) {
        return;
      }

      const fresh = items.filter(item => {
        if (!item || !item.id) return false;
        if (seenIds.has(String(item.id))) return false;
        const createdAt = Date.parse(item.created_at || '');
        if (Number.isFinite(createdAt) && createdAt <= sessionStartedAt) return false;
        return true;
      });

      fresh.slice().reverse().forEach(item => {
        playNotificationSound();
        notifyBrowser(kind, item);
        seenIds.add(String(item.id));
      });

      writeJsonArray(seenKey, Array.from(seenIds));
    }

    function setupBrowserNotifPermissionHint(requestFn){
      if (!browserNotifSupport()) return;
      if (browserNotifPermission() !== 'default') return;
      const once = async () => {
        await requestFn();
        window.removeEventListener('click', once, true);
        window.removeEventListener('keydown', once, true);
      };
      window.addEventListener('click', once, true);
      window.addEventListener('keydown', once, true);
    }

    function layoutState(){
      return {
        sidebarOpen: true,
        mobileOpen: false,
        _loaderTimeout: null,

        init(){
          // restore sidebar state
          const saved = localStorage.getItem('sidebarOpen');
          if (saved !== null) this.sidebarOpen = saved === '1';

          // stop loader saat siap / kembali dari bfcache
          window.addEventListener('DOMContentLoaded', stopLoading, { once:true });
          window.addEventListener('pageshow', stopLoading);

          // start loader sebelum unload
          window.addEventListener('beforeunload', startLoading);

          // klik link internal → start loader (kecuali download / data-noloader / new tab / hash)
          document.addEventListener('click', (e)=>{
            const a = e.target.closest('a');
            if (!a) return;

            // abaikan link tertentu
            if (a.hasAttribute('download') || a.dataset.noloader === '1') return;

            const href = a.getAttribute('href') || '';
            if (!href || href.startsWith('#')) return;
            if (a.target === '_blank' || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

            try{
              const u = new URL(href, location.href);
              if (u.origin !== location.origin) return;
              startLoading();
            }catch(_){}
          }, true);

          // submit form (kecuali target _blank / form tertentu tanpa loader)
          document.addEventListener('submit', (e)=>{
            const f = e.target;
            if (f && f.target === '_blank') return;
            if (f && f.dataset && f.dataset.noloader === '1') return;
            startLoading();
          }, true);
        },

        toggleSidebar(){
          this.sidebarOpen = !this.sidebarOpen;
          localStorage.setItem('sidebarOpen', this.sidebarOpen ? '1' : '0');
        }
      }
    }

    function startLoading(){
      // Tampilkan spinner dengan sedikit delay agar navigasi cepat tidak "flash"
      if (window.NProgress) NProgress.start();
      clearTimeout(window.__loaderShowDelay);
      clearTimeout(window.__loaderTimeout);
      window.__loaderShowDelay = setTimeout(()=>{
        document.documentElement.classList.add('loading');
      }, 120);
      window.__loaderTimeout = setTimeout(stopLoading, 15000);
    }

    function stopLoading(){
      clearTimeout(window.__loaderShowDelay);
      clearTimeout(window.__loaderTimeout);
      requestAnimationFrame(()=>{
        document.documentElement.classList.remove('loading');
        if (window.NProgress) NProgress.done();
      });
    }
  </script>

  <script>
    // Auto-hide session flash toasts after 3 seconds
    document.addEventListener('DOMContentLoaded', ()=>{
      const flashes = document.querySelectorAll('div.mb-4.rounded-lg');
      if(!flashes.length) return;
      setTimeout(()=>{
        flashes.forEach(el=>{
          el.style.transition = 'opacity .25s ease';
          el.style.opacity = '0';
          setTimeout(()=>{ try{ el.remove(); }catch(_){} }, 300);
        });
      }, 3000);
    });
  </script>

  <script>
    function notifState(){
      return {
        open: false,
        count: 0,
        items: [],
        timer: null,
        _sessionStartedAt: Date.now(),
        init(){
          setupBrowserNotifPermissionHint(requestBrowserNotifPermission);
          setBrowserNotifEnabled(browserNotifEnabled());
          this._sessionStartedAt = Date.now();
          this.fetchNow();
          this.timer = setInterval(()=>this.fetchNow(), 30000);
        },
        toggle(){ this.open = !this.open; if(this.open) this.fetchNow(); },
        badgeText(){ return this.count>99 ? '99+' : String(this.count); },
        async toggleDesktopNotifications(){
          if (browserNotifEnabled()) {
            setBrowserNotifEnabled(false);
            this.desktopNotificationFeedback('error');
            return;
          }

          const perm = await requestBrowserNotifPermission();
          if (perm === 'granted') {
            setBrowserNotifEnabled(true);
            this.desktopNotificationFeedback('success', true);
            this.fetchNow();
          } else if (perm === 'denied') {
            setBrowserNotifEnabled(false);
            this.desktopNotificationFeedback('error');
          }
        },
        async enableDesktopNotifications(){
          if (browserNotifPermission() === 'granted') {
            this.desktopNotificationFeedback('success', true);
            return;
          }
          const perm = await requestBrowserNotifPermission();
          if (perm === 'granted') {
            this.desktopNotificationFeedback('success', true);
            this.fetchNow();
          } else if (perm === 'denied') {
            this.desktopNotificationFeedback('error');
          }
        },
        desktopNotificationFeedback(type, instant = false){
          if (type === 'success') {
            if (instant) {
              playNotificationSound();
              if (browserNotifEnabled() && browserNotifPermission() === 'granted') {
                notifyBrowser('notification', {
                  title: 'Notifikasi diaktifkan',
                  body: 'Popup desktop berhasil diaktifkan.',
                  url: window.location.href,
                  id: `notif-success-${Date.now()}`,
                  ticket_no: '-',
                  created_at: new Date().toISOString(),
                });
              }
              return;
            }

            playNotificationSound();
            return;
          }

          playNotificationSound();
          if (browserNotifEnabled() && browserNotifPermission() === 'granted') {
            notifyBrowser('notification', {
              title: 'Notifikasi diblokir',
              body: 'Popup desktop diblokir oleh browser.',
              url: window.location.href,
              id: `notif-error-${Date.now()}`,
              ticket_no: '-',
              created_at: new Date().toISOString(),
            });
          }
        },
        async fetchNow(){
          try{
            const res = await fetch('<?php echo e(route('notifications.index')); ?>', { headers: { 'Accept':'application/json' } });
            if(!res.ok) return;
            const json = await res.json();
            notifyFromResponse('notification', json, this);
          }catch(_){ }
        },
        async markAll(){
          try{
            const res = await fetch('<?php echo e(route('notifications.readAll')); ?>', {
              method: 'POST',
              headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept':'application/json'
              }
            });
            if(res.ok){ this.count = 0; this.fetchNow(); }
          }catch(_){ }
        },
        async markOne(n){
          try{
            const res = await fetch('<?php echo e(route('notifications.readOne', ':id')); ?>'.replace(':id', encodeURIComponent(n.id)), {
              method: 'POST',
              headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').content,
                'Accept':'application/json'
              }
            });
            // Decrease badge locally even if network is slow
            if(this.count>0) this.count--;
            // Navigate after marking
            if(n.url){
              window.location.href = n.url;
            }
          }catch(_){ if(n.url){ window.location.href = n.url; } }
        },
        formatTime(iso){
          try{ const d = new Date(iso); return d.toLocaleString(); }catch(_){ return ''; }
        }
      }
    }

    function commentNotifState(){
      return {
        open: false,
        count: 0,
        items: [],
        timer: null,
        _sessionStartedAt: Date.now(),
        init(){
          this._sessionStartedAt = Date.now();
          this.fetchNow();
          this.timer = setInterval(()=>this.fetchNow(), 30000);
        },
        toggle(){ this.open = !this.open; if(this.open) this.fetchNow(); },
        badgeText(){ return this.count>99 ? '99+' : String(this.count); },
        async fetchNow(){
          try{
            const res = await fetch('<?php echo e(route('notifications.comments')); ?>', { headers: { 'Accept':'application/json' } });
            if(!res.ok) return;
            const json = await res.json();
            notifyFromResponse('comment', json, this);
          }catch(_){ }
        },
        async markAll(){
          try{
            const res = await fetch('<?php echo e(route('notifications.comments.readAll')); ?>', {
              method: 'POST',
              headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept':'application/json'
              }
            });
            if(res.ok){ this.count = 0; this.fetchNow(); }
          }catch(_){ }
        },
        async markOne(n){
          try{
            const res = await fetch('<?php echo e(route('notifications.readOne', ':id')); ?>'.replace(':id', encodeURIComponent(n.id)), {
              method: 'POST',
              headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').content,
                'Accept':'application/json'
              }
            });
            if(this.count>0) this.count--;
            if(n.url){
              window.location.href = n.url;
            }
          }catch(_){ if(n.url){ window.location.href = n.url; } }
        },
        formatTime(iso){
          try{ const d = new Date(iso); return d.toLocaleString(); }catch(_){ return ''; }
        }
      }
    }
  </script>

  
  <div id="toast-root" class="fixed top-4 left-1/2 -translate-x-1/2 z-[1000] space-y-2 pointer-events-none">
    <?php if(session('success')): ?>
      <div class="pointer-events-auto rounded-lg bg-green-50 text-green-700 px-4 py-3 border border-green-200 shadow-md">
        <?php echo e(session('success')); ?>

      </div>
    <?php endif; ?>
    <?php if(session('error')): ?>
      <div class="pointer-events-auto rounded-lg bg-red-50 text-red-700 px-4 py-3 border border-red-200 shadow-md">
        <?php echo e(session('error')); ?>

      </div>
    <?php endif; ?>
  </div>

  <?php if(auth()->guard()->check()): ?>
  <?php if($aiChatEnabled && $aiChatEnabledForUser): ?>
  
  <div x-data="appAssistantState()" x-init="init()" x-cloak class="hidden md:block fixed bottom-5 right-5 z-[950]">
    <button type="button"
            @click="toggle()"
            class="ai-fab hd-ai-fab inline-flex items-center gap-2 rounded-full px-4 py-2.5 text-sm font-semibold text-white hover:opacity-95 focus:outline-none focus:ring-2 focus:ring-hd-400 focus:ring-offset-2">
      <span>AI</span>
      <span>Assistant</span>
    </button>

    <div x-show="open"
         x-transition.opacity
         x-transition.scale.origin.bottom.right
         @click.outside="open=false"
         class="ai-panel absolute bottom-14 right-0 w-[22rem] overflow-hidden rounded-2xl border border-gray-200 bg-white">
      <div class="flex items-center justify-between border-b bg-gray-50 px-3 py-2">
        <div>
          <p class="text-sm font-semibold text-gray-800">AI Assistant</p>
          <p class="text-[11px] text-gray-500">Panduan operasional aplikasi</p>
        </div>
        <button @click="open=false" class="h-7 w-7 rounded-md text-gray-500 hover:bg-gray-200 hover:text-gray-700" aria-label="Tutup">✕</button>
      </div>

      <div class="max-h-80 space-y-2 overflow-y-auto bg-white p-3" x-ref="chatList">
        <template x-for="(msg, idx) in messages" :key="idx">
          <div :class="msg.role === 'user' ? 'text-right' : 'text-left'">
            <div :class="msg.role === 'user'
                          ? 'inline-block max-w-[92%] rounded-2xl rounded-br-sm bg-hd-500 px-3 py-2 text-xs text-white'
                          : 'inline-block max-w-[92%] rounded-2xl rounded-bl-sm bg-gray-100 px-3 py-2 text-xs text-gray-800'">
              <span class="whitespace-pre-line break-words" x-text="msg.text"></span>
            </div>
          </div>
        </template>
      </div>

      <div class="border-t bg-white p-3">
        <form @submit.prevent="submit()" data-noloader="1" class="flex items-end gap-2">
          <textarea x-model="input"
                    @keydown.enter.prevent="submit()"
                    rows="2"
                    placeholder="Tanya cara pakai aplikasi..."
                    class="w-full resize-none rounded-lg border-gray-300 text-xs hd-focus"></textarea>
          <button type="submit"
                  :disabled="isSending"
                  class="shrink-0 rounded-lg bg-hd-500 px-3 py-2 text-xs font-medium text-white hover:bg-hd-600 disabled:cursor-not-allowed disabled:opacity-60">
            <span x-text="isSending ? 'Memproses pesan ...' : 'Kirim'"></span>
          </button>
        </form>
      </div>
    </div>
  </div>
  <?php endif; ?>
  <?php endif; ?>

  <script>
    // Auto-hide toasts after 3 seconds without shifting layout
    document.addEventListener('DOMContentLoaded', ()=>{
      const root = document.getElementById('toast-root');
      if(!root) return;
      const toasts = Array.from(root.children);
      if(!toasts.length) return;
      setTimeout(()=>{
        toasts.forEach(el=>{
          el.style.transition = 'opacity .25s ease, transform .25s ease';
          el.style.opacity = '0';
          el.style.transform = 'translateY(8px)';
          setTimeout(()=>{ try{ el.remove(); }catch(_){} }, 300);
        });
      }, 3000);
    });
  </script>

  <?php if(auth()->guard()->check()): ?>
  <script>
    function appAssistantState(){
      return {
        open: false,
        input: '',
        messages: [],
        isSending: false,
        links: {
          create: '<?php echo e(route(auth()->user()->role === 'IT' ? 'it.ticket.create' : 'cabang.dashboard')); ?>',
          allTickets: '<?php echo e(auth()->user()->role === 'IT' ? route('it.dashboard') : (auth()->user()->role === 'VENDOR' ? route('vendor.tickets') : route('cabang.tickets'))); ?>',
          myTickets: '<?php echo e(auth()->user()->role === 'IT' ? route('it.my') : (auth()->user()->role === 'VENDOR' ? route('vendor.tickets') : route('cabang.tickets'))); ?>',
          stats: '<?php echo e(auth()->user()->role === 'IT' ? route('it.stats') : route('dashboard')); ?>',
          params: '<?php echo e(auth()->user()->role === 'IT' ? route('it.parameters') : route('dashboard')); ?>',
          profile: '<?php echo e(auth()->user()->role === 'VENDOR' ? route('vendor.profile.edit') : route('profile.edit')); ?>'
        },
        init(){
          const name = <?php echo json_encode(auth()->user()->name ?? 'User', 15, 512) ?>;
          this.messages = [
            { role:'assistant', text:`Halo ${name}. Saya bantu operasional Helpdesk. Coba tanya: "cara buat tiket", "update status", atau "kelola parameter".` }
          ];
          this.loadHistory();
        },
        async loadHistory(){
          try{
            const res = await fetch('<?php echo e(route('assistant.history')); ?>', {
              headers: { 'Accept': 'application/json' },
            });
            if (!res.ok) return;
            const json = await res.json();
            const items = Array.isArray(json?.messages) ? json.messages : [];
            if (!items.length) return;
            this.messages = items.map(m => ({
              role: m.role === 'user' ? 'user' : 'assistant',
              text: String(m.text || ''),
            }));
          }catch(_){}
        },
        toggle(){
          this.open = !this.open;
          this.$nextTick(() => this.scrollToBottom());
        },
        submit(){
          const q = (this.input || '').trim();
          if(!q || this.isSending) return;
          this.ask(q);
          this.input = '';
        },
        async ask(q){
          this.messages.push({ role:'user', text:q });
          this.isSending = true;
          this.messages.push({ role:'assistant', text:'Memproses pesan ...' });
          this.$nextTick(() => this.scrollToBottom());
          const reply = await this.requestAi(q);
          this.messages[this.messages.length - 1] = { role:'assistant', text:reply };
          this.isSending = false;
          this.$nextTick(() => this.scrollToBottom());
        },
        async requestAi(raw){
          const q = String(raw || '').trim();
          const history = this.messages
            .filter(m => (m.role === 'user' || m.role === 'assistant') && m.text !== 'Memproses pesan ...')
            .slice(-8)
            .map(m => ({ role: m.role, text: m.text }));

          try{
            const res = await fetch('<?php echo e(route('assistant.chat')); ?>', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
              },
              body: JSON.stringify({ message: q, history }),
            });
            if (!res.ok) {
              throw new Error('HTTP ' + res.status);
            }
            const json = await res.json();
            const reply = (json && typeof json.reply === 'string') ? json.reply.trim() : '';
            if (reply) return reply;
          }catch(_){}

          return this.fallbackAnswer(q);
        },
        fallbackAnswer(raw){
          const q = String(raw || '').toLowerCase();
          if (/(buat|create|input).*(tiket)|cara buat tiket|ticket baru/.test(q)) {
            return `Untuk membuat tiket:\n1) Buka menu Input/Buat Tiket.\n2) Isi kategori, deskripsi, dan lampiran (opsional).\n3) Klik Simpan/Kirim.\nLink cepat: ${this.links.create}`;
          }
          if (/(all|semua|list).*(tiket)|dashboard tiket/.test(q)) {
            return `Untuk melihat daftar tiket, buka halaman All Tiket.\nGunakan filter status/kategori untuk mempercepat pencarian.\nLink cepat: ${this.links.allTickets}`;
          }
          if (/(tiket saya|my ticket|my tiket)/.test(q)) {
            return `Halaman Tiket Saya menampilkan tiket sesuai peran Anda.\nLink cepat: ${this.links.myTickets}`;
          }
          if (/(status|update|assign|vendor|close|reopen)/.test(q)) {
            return 'Update tiket dilakukan dari halaman detail tiket:\n- tombol Update (ubah status/assign vendor)\n- tombol Close ticket untuk penutupan\n- tombol Re-open jika tiket sudah CLOSED.';
          }
          if (/(komentar|chat|lampiran)/.test(q)) {
            return 'Di detail tiket, gunakan panel Komentar/Progres untuk kirim pesan dan lampiran. Anda juga bisa tempel gambar (Ctrl+V) langsung ke kolom komentar.';
          }
          if (/(statistik|stats|laporan|report)/.test(q)) {
            return `Untuk statistik, buka menu Statistik lalu atur filter periode/status.\nLink cepat: ${this.links.stats}`;
          }
          if (/(parameter|kategori|subkategori|root cause)/.test(q)) {
            return `Kelola kategori/subkategori/root cause di menu Parameter.\nLink cepat: ${this.links.params}`;
          }
          if (/(profil|profile|akun|password)/.test(q)) {
            return `Pengaturan akun ada di halaman Profil.\nLink cepat: ${this.links.profile}`;
          }
          return 'Saya bisa bantu panduan operasional aplikasi Helpdesk. Coba kata kunci: buat tiket, all tiket, tiket saya, update status, komentar, statistik, parameter, atau profil.';
        },
        scrollToBottom(){
          const el = this.$refs.chatList;
          if(el){ el.scrollTop = el.scrollHeight; }
        }
      }
    }
  </script>
  <?php endif; ?>

  <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\laragon\www\helpdesk-app\resources\views/layouts/app.blade.php ENDPATH**/ ?>