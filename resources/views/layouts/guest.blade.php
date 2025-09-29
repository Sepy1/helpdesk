<!doctype html>
<html lang="{{ str_replace('_','-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>@yield('title', config('app.name', 'Helpdesk'))</title>

  {{-- Fonts (opsional) --}}
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,800&display=swap" rel="stylesheet" />

  {{-- Vite assets --}}
  @vite(['resources/css/app.css','resources/js/app.js'])

  {{-- Alpine & x-cloak --}}
  <style>[x-cloak]{display:none!important}</style>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

  {{-- NProgress (progress bar di atas) --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nprogress@0.2.0/nprogress.css">
  <script defer src="https://cdn.jsdelivr.net/npm/nprogress@0.2.0/nprogress.js"></script>

  {{-- Loader & transisi halus --}}
  <style>
    .spin { animation: spin 1s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg) } }

    /* Progress bar tune */
    #nprogress .bar { background:#4f46e5 !important; height:3px !important; }
    #nprogress .peg { box-shadow:0 0 10px #4f46e5,0 0 5px #4f46e5 !important; }

    /* Konten fade/slide saat loading */
    .page-root {
      opacity: 1;
      transform: none;
      transition: opacity .28s ease, transform .32s ease;
      will-change: opacity, transform;
    }
    html.loading .page-root {
      opacity: 0;
      transform: translateY(6px);
    }

    /* Loader overlay: gunakan opacity (bukan display:none) untuk transisi mulus */
    #page-loader {
      display: flex;                 /* selalu terpasang */
      opacity: 0;                    /* default tersembunyi */
      pointer-events: none;          /* tak blok klik saat tersembunyi */
      transition: opacity .22s ease; /* halus masuk/keluar */
    }
    html.loading #page-loader {
      opacity: 1;
      pointer-events: all;
    }

    /* Hormati preferensi aksesibilitas */
    @media (prefers-reduced-motion: reduce) {
      .page-root, #page-loader { transition: none !important; }
    }
  </style>
</head>
<body class="h-full font-sans antialiased">
  {{-- Konten halaman tamu (login, forgot password, dll) --}}
  <div id="page-root" class="page-root">
    @yield('content')
  </div>

  {{-- Overlay loader (global) --}}
  <div id="page-loader" class="fixed inset-0 z-[9999] items-center justify-center bg-white/70 backdrop-blur-sm">
    <div class="flex flex-col items-center gap-3">
      <svg class="h-8 w-8 text-indigo-600 spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
      </svg>
      <div class="text-sm text-gray-700">Memuat…</div>
    </div>
  </div>

  {{-- Loader & nav wiring --}}
  <script>
    (function () {
      function startLoading() {
        document.documentElement.classList.add('loading');
        if (window.NProgress) NProgress.start();
      }
      function stopLoading() {
        // frame berikut agar CSS transition terlihat
        requestAnimationFrame(() => {
          document.documentElement.classList.remove('loading');
          if (window.NProgress) NProgress.done();
        });
      }

      // Stop saat halaman siap/ditampilkan kembali
      window.addEventListener('DOMContentLoaded', stopLoading);
      window.addEventListener('pageshow', stopLoading);
      // Mulai saat akan berpindah halaman
      window.addEventListener('beforeunload', startLoading);

      // Klik link internal
      document.addEventListener('click', function (e) {
        const a = e.target.closest('a');
        if (!a) return;
        const href = a.getAttribute('href') || '';
        if (!href || href.startsWith('#') || a.hasAttribute('download')) return;
        if (a.target === '_blank' || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        try {
          const u = new URL(href, location.href);
          if (u.origin !== location.origin) return;
          startLoading();
        } catch (_) {}
      }, true);

      // Submit form
      document.addEventListener('submit', function () { startLoading(); }, true);
    })();
  </script>

  @stack('scripts')
</body>
</html>
