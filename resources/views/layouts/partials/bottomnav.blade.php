@php
  $role = auth()->user()->role ?? 'CABANG';

  // helper aktif
  $is = fn($patterns) => request()->routeIs(...(array)$patterns);

  // item nav per role
  $items = $role === 'IT'
    ? [
        ['label'=>'Daftar',   'route'=>'it.dashboard',   'active'=>$is('it.dashboard'),   'icon'=>'list'],
        ['label'=>'Saya',     'route'=>'it.my',          'active'=>$is('it.my'),          'icon'=>'user'],
        ['label'=>'Stat',     'route'=>'it.stats',       'active'=>$is('it.stats'),       'icon'=>'chart'],
      ]
    : [
        ['label'=>'Buat',     'route'=>'cabang.dashboard','active'=>$is('cabang.dashboard'),'icon'=>'plus'],
        ['label'=>'Tiket',    'route'=>'cabang.tickets', 'active'=>$is('cabang.tickets*'), 'icon'=>'folder'],
    
      ];

  // svg ikon
  $icons = [
    'list' => '<path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
    'user' => '<path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.418 0-8 2.239-8 5v1h16v-1c0-2.761-3.582-5-8-5Z" fill="currentColor"/>',
    'chart'=> '<path d="M4 20h16M7 17V9m5 8V4m5 13v-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
    'plus' => '<path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
    'folder'=>'<path d="M3 7h6l2 2h10v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z" stroke="currentColor" stroke-width="2" fill="none"/>',
    'settings'=>'<path d="M12 15a3 3 0 1 0-3-3 3 3 0 0 0 3 3Zm7.94-3.06a7.78 7.78 0 0 0 0-1.88l2.06-1.6a.5.5 0 0 0 .12-.64l-2-3.46a.5.5 0 0 0-.6-.22l-2.43 1a7.39 7.39 0 0 0-1.62-.94l-.37-2.6a.5.5 0 0 0-.5-.42h-4a.5.5 0 0 0-.5.42l-.37 2.6a7.39 7.39 0 0 0-1.62.94l-2.43-1a.5.5 0 0 0-.6.22l-2 3.46a.5.5 0 0 0 .12.64l2.06 1.6a7.78 7.78 0 0 0 0 1.88l-2.06 1.6a.5.5 0 0 0-.12.64l2 3.46a.5.5 0 0 0 .6.22l2.43-1a7.39 7.39 0 0 0 1.62.94l.37 2.6a.5.5 0 0 0 .5.42h4a.5.5 0 0 0 .5-.42l.37-2.6a7.39 7.39 0 0 0 1.62-.94l2.43 1a.5.5 0 0 0 .6-.22l2-3.46a.5.5 0 0 0-.12-.64Z" fill="none" stroke="currentColor" stroke-width="1.5"/>'
  ];
@endphp

<<nav
  class="fixed bottom-0 inset-x-0 z-40 border-t bg-white/95 backdrop-blur md:hidden"
  role="navigation" aria-label="Bottom Navigation"
>
  <ul class="grid grid-cols-3 place-items-stretch">
    {{-- Buat tiket --}}
    <li>
      <a href="{{ route('cabang.dashboard') }}"
         class="flex flex-col items-center justify-center gap-1 py-3
                {{ request()->routeIs('cabang.dashboard') ? 'text-indigo-600' : 'text-gray-600' }}">
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <!-- Plus icon -->
          <path d="M12 5v14M5 12h14"/>
        </svg>
        <span class="text-xs">Buat</span>
      </a>
    </li>

    {{-- Tiket saya --}}
    <li>
      <a href="{{ route('cabang.tickets') }}"
         class="flex flex-col items-center justify-center gap-1 py-3
                {{ request()->routeIs('cabang.tickets') ? 'text-indigo-600' : 'text-gray-600' }}">
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <!-- List icon -->
          <path d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
        <span class="text-xs">Tiket</span>
      </a>
    </li>

    {{-- Profil --}}
   
  </ul>
</nav>