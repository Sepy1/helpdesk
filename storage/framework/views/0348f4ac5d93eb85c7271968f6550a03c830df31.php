<!doctype html>
<html lang="id" class="h-full bg-grey-50 loading">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
  <meta name="theme-color" content="#ffffff">
  <title><?php echo $__env->yieldContent('title','Helpdesk'); ?></title>
 <style>[x-cloak]{display:none!important}</style>
  <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css','resources/js/app.js']); ?>

  
  <style>[x-cloak]{display:none!important}</style>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

  
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nprogress@0.2.0/nprogress.css">
  <script defer src="https://cdn.jsdelivr.net/npm/nprogress@0.2.0/nprogress.js"></script>

  <style>
    .spin{animation:spin 1s linear infinite}@keyframes spin{to{transform:rotate(360deg)}}
    #nprogress .bar{background:#4f46e5!important;height:3px!important}
    #nprogress .peg{box-shadow:0 0 10px #4f46e5,0 0 5px #4f46e5!important}

    /* konten smooth-in saat berpindah */
    .page-root{opacity:1;transform:none;transition:opacity .32s ease, transform .36s ease;will-change:opacity,transform}
    html.loading .page-root{opacity:0;transform:translateY(8px)}

    /* overlay loader */
    #page-loader{display:flex;opacity:0;pointer-events:none;transition:opacity .24s ease}
    html.loading #page-loader{opacity:1;pointer-events:auto}

    /* sidebar fade (opsional) */
    aside[aria-label="Sidebar"]>div{transition:opacity .25s ease}

    @media (prefers-reduced-motion:reduce){
      .page-root,#page-loader,aside[aria-label="Sidebar"]>div{transition:none!important}
    }
  </style>

  
  <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body
  class="h-full font-sans antialiased"
  x-data="layoutState()"
  x-init="init()"
  :class="{ 'overflow-hidden': mobileOpen }"
>

  
<header class="bg-gradient-to-r from-blue-700 via-indigo-600 to-violet-600 border-b sticky top-0 z-40">
  <div class="w-full h-16 px-4 sm:px-6 lg:px-8 flex items-center justify-between">
    <div class="flex items-center gap-2 min-w-0">
      <div class="h-9 w-9 rounded-xl bg-white/15 flex items-center justify-center shrink-0">
        <span class="text-white font-bold">HD</span>
      </div>
      <span class="font-semibold text-white truncate">Helpdesk</span>

      <button type="button"
              class="hidden md:inline-flex ml-2 h-9 w-9 items-center justify-center rounded-lg hover:bg-white/10 text-white"
              @click="toggleSidebar()" :aria-pressed="sidebarOpen.toString()"
              aria-label="Tampilkan/sembunyikan menu">☰</button>

      <button type="button"
              class="inline-flex md:hidden ml-1 h-9 w-9 items-center justify-center rounded-lg hover:bg-white/10 text-white"
              @click="mobileOpen = true" aria-controls="mobile-drawer" aria-expanded="true" aria-label="Buka menu">☰</button>
    </div>

    <nav class="flex items-center gap-3 text-sm min-w-0">
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
          <div x-show="open" x-cloak @click.outside="open=false"
               class="absolute right-0 mt-2 w-80 max-w-[90vw] bg-white rounded-xl shadow-lg ring-1 ring-gray-200 overflow-hidden z-50">
            <div class="flex items-center justify-between px-3 py-2 border-b">
              <div class="font-medium text-gray-800">Notifikasi</div>
              <button @click="markAll()" class="text-xs text-indigo-600 hover:underline">Tandai sudah dibaca</button>
            </div>
            <div class="max-h-80 overflow-auto">
              <template x-if="items.length===0">
                <div class="px-3 py-4 text-sm text-gray-500">Tidak ada notifikasi.</div>
              </template>
              <template x-for="n in items" :key="n.id">
                <a :href="n.url || '#'" class="block px-3 py-2 hover:bg-gray-50" @click.prevent="open=false; markOne(n);">
                  <div class="text-sm font-medium text-gray-800" x-text="n.title"></div>
                  <div class="text-xs text-gray-600 mt-0.5" x-text="n.body"></div>
                  <div class="text-[11px] text-gray-400 mt-0.5" x-text="formatTime(n.created_at)"></div>
                </a>
              </template>
            </div>
          </div>
        </div>
        <span class="hidden sm:inline text-white/80 truncate max-w-[40ch]">
          <?php echo e(auth()->user()->name); ?> — <span class="uppercase"><?php echo e(auth()->user()->role); ?></span>
        </span>
        <form method="POST" action="<?php echo e(route('logout')); ?>" class="shrink-0">
          <?php echo csrf_field(); ?>
          <button type="submit"
            class="inline-flex items-center px-3 py-1.5 rounded-lg bg-white/15 text-white hover:bg-white/25 focus:outline-none focus:ring-2 focus:ring-white/50">
            Logout
          </button>
        </form>
      <?php endif; ?>
    </nav>
  </div>
</header>


  
  <div class="relative">
    
    <aside aria-label="Sidebar"
           class="hidden md:block fixed z-30 top-16 bottom-0 left-0 w-64 border-r bg-white overflow-y-auto
                  transition-transform duration-200 will-change-transform"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
      <div class="h-full p-4">
        <?php echo $__env->make('layouts.partials.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      </div>
    </aside>

    
    <div class="pt-6 px-4 sm:px-6 lg:px-8 transition-[margin] duration-200
                pb-[calc(env(safe-area-inset-bottom)+72px)] md:pb-0"
         :class="sidebarOpen ? 'md:ml-64' : 'md:ml-0'">
      <?php if(session('success')): ?>
        <div class="mb-4 rounded-lg bg-green-50 text-green-700 px-4 py-3 border border-green-200"><?php echo e(session('success')); ?></div>
      <?php endif; ?>
      <?php if(session('error')): ?>
        <div class="mb-4 rounded-lg bg-red-50 text-red-700 px-4 py-3 border border-red-200"><?php echo e(session('error')); ?></div>
      <?php endif; ?>

      <div id="page-root" class="page-root">
        <?php echo $__env->yieldContent('content'); ?>
      </div>
    </div>
  </div>

  
  <div id="mobile-drawer" x-show="mobileOpen" x-cloak
       class="fixed inset-0 z-50 md:hidden" role="dialog" aria-modal="true" aria-label="Menu navigasi">
    <div class="absolute inset-0 bg-black/40" @click="mobileOpen=false" aria-hidden="true"></div>
    <div class="absolute left-0 top-0 bottom-0 w-72 bg-white p-4 shadow-xl focus:outline-none">
      <div class="mb-4 flex justify-between items-center">
        <div class="font-semibold text-gray-800">Menu</div>
        <button type="button" class="h-8 w-8 inline-flex items-center justify-center rounded-md hover:bg-gray-100"
                @click="mobileOpen=false" aria-label="Tutup menu">✕</button>
      </div>
      <?php echo $__env->make('layouts.partials.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
  </div>

  
  <div id="page-loader"
       class="fixed inset-0 z-[9999] items-center justify-center bg-white/70 backdrop-blur-sm"
       aria-live="polite" aria-busy="true">
    <div class="flex flex-col items-center gap-3">
      <svg class="h-8 w-8 text-indigo-600 spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
      </svg>
      <div class="text-sm text-gray-700">Memuat…</div>
    </div>
  </div>

  
  <?php echo $__env->make('layouts.partials.bottomnav', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

  <script>
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

          // submit form (kecuali target _blank)
          document.addEventListener('submit', (e)=>{
            const f = e.target;
            if (f && f.target === '_blank') return;
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
      // fallback auto-stop 15s agar tidak "menggantung" jika ada error
      const s = document.documentElement;
      s.classList.add('loading');
      if (window.NProgress) NProgress.start();
      clearTimeout(window.__loaderTimeout);
      window.__loaderTimeout = setTimeout(stopLoading, 15000);
    }

    function stopLoading(){
      clearTimeout(window.__loaderTimeout);
      requestAnimationFrame(()=>{
        document.documentElement.classList.remove('loading');
        if (window.NProgress) NProgress.done();
      });
    }
  </script>

  <script>
    function notifState(){
      return {
        open: false,
        count: 0,
        items: [],
        timer: null,
        init(){ this.fetchNow(); this.timer = setInterval(()=>this.fetchNow(), 30000); },
        toggle(){ this.open = !this.open; if(this.open) this.fetchNow(); },
        badgeText(){ return this.count>99 ? '99+' : String(this.count); },
        async fetchNow(){
          try{
            const res = await fetch('<?php echo e(route('notifications.index')); ?>', { headers: { 'Accept':'application/json' } });
            if(!res.ok) return;
            const json = await res.json();
            this.count = json.unread || 0;
            this.items = json.items || [];
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
  </script>

  <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\laragon\www\helpdesk-app\resources\views/layouts/app.blade.php ENDPATH**/ ?>