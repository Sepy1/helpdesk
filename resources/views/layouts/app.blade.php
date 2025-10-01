<!doctype html>
<html lang="id" class="h-full bg-grey-50 loading">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="theme-color" content="#ffffff">
  <title>@yield('title','Helpdesk')</title>

  @vite(['resources/css/app.css','resources/js/app.js'])

  {{-- Alpine --}}
  <style>[x-cloak]{display:none!important}</style>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

  {{-- NProgress --}}
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

  {{-- Styles dari child (mis. timeline) --}}
  @stack('styles')
</head>
<body
  class="h-full font-sans antialiased"
  x-data="layoutState()"
  x-init="init()"
  :class="{ 'overflow-hidden': mobileOpen }"
>

  {{-- TOPBAR (gradient) --}}
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
      @auth
        <span class="hidden sm:inline text-white/80 truncate max-w-[40ch]">
          {{ auth()->user()->name }} — <span class="uppercase">{{ auth()->user()->role }}</span>
        </span>
        <form method="POST" action="{{ route('logout') }}" class="shrink-0">
          @csrf
          <button type="submit"
            class="inline-flex items-center px-3 py-1.5 rounded-lg bg-white/15 text-white hover:bg-white/25 focus:outline-none focus:ring-2 focus:ring-white/50">
            Logout
          </button>
        </form>
      @endauth
    </nav>
  </div>
</header>


  {{-- ====== LAYOUT: SIDEBAR KIRI + KONTEN ====== --}}
  <div class="relative">
    {{-- Sidebar (desktop) --}}
    <aside aria-label="Sidebar"
           class="hidden md:block fixed z-30 top-16 bottom-0 left-0 w-64 border-r bg-white overflow-y-auto
                  transition-transform duration-200 will-change-transform"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
      <div class="h-full p-4">
        @include('layouts.partials.sidebar')
      </div>
    </aside>

    {{-- Konten --}}
    <div class="pt-6 px-4 sm:px-6 lg:px-8 transition-[margin] duration-200
                pb-[calc(env(safe-area-inset-bottom)+72px)] md:pb-0"
         :class="sidebarOpen ? 'md:ml-64' : 'md:ml-0'">
      @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 text-green-700 px-4 py-3 border border-green-200">{{ session('success') }}</div>
      @endif
      @if(session('error'))
        <div class="mb-4 rounded-lg bg-red-50 text-red-700 px-4 py-3 border border-red-200">{{ session('error') }}</div>
      @endif

      <div id="page-root" class="page-root">
        @yield('content')
      </div>
    </div>
  </div>

  {{-- Drawer Mobile --}}
  <div id="mobile-drawer" x-show="mobileOpen" x-cloak
       class="fixed inset-0 z-50 md:hidden" role="dialog" aria-modal="true" aria-label="Menu navigasi">
    <div class="absolute inset-0 bg-black/40" @click="mobileOpen=false" aria-hidden="true"></div>
    <div class="absolute left-0 top-0 bottom-0 w-72 bg-white p-4 shadow-xl focus:outline-none">
      <div class="mb-4 flex justify-between items-center">
        <div class="font-semibold text-gray-800">Menu</div>
        <button type="button" class="h-8 w-8 inline-flex items-center justify-center rounded-md hover:bg-gray-100"
                @click="mobileOpen=false" aria-label="Tutup menu">✕</button>
      </div>
      @include('layouts.partials.sidebar')
    </div>
  </div>

  {{-- Overlay loader (global) --}}
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

  {{-- Bottom nav mobile --}}
  @include('layouts.partials.bottomnav')

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

  @stack('scripts')
</body>
</html>
