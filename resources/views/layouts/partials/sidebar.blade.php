{{-- resources/views/layouts/partials/sidebar.blade.php --}}
@php
  $user  = auth()->user();
  $role  = $user->role ?? 'CABANG';
  $is    = fn(...$names) => request()->routeIs(...$names);

  // Kumpulan ikon (SVG inline)
  $icons = [
    'home'     => '<path d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-9.5Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />',
    'plus'     => '<path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />',
    'list'     => '<path d="M4 7h16M4 12h16M4 17h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />',
    'user'     => '<path d="M12 12a4.5 4.5 0 1 0-4.5-4.5A4.5 4.5 0 0 0 12 12Zm0 2c-4 0-7 2.2-7 5v1h14v-1c0-2.8-3-5-7-5Z" fill="currentColor" />',
    'chart'    => '<path d="M4 20h16M7 17V9m5 8V4m5 13v-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />',
    'settings' => '<path d="M12 15a3 3 0 1 0-3-3 3 3 0 0 0 3 3Zm7.8-3a7.7 7.7 0 0 0-.1-1.4l2-1.6a.8.8 0 0 0 .2-.9l-2-3.4a.8.8 0 0 0-.9-.3l-2.3 1a8.2 8.2 0 0 0-1.4-.8l-.4-2.5a.8.8 0 0 0-.8-.7h-4a.8.8 0 0 0-.8.7l-.4 2.5a8.2 8.2 0 0 0-1.4.8l-2.3-1a.8.8 0 0 0-.9.3l-2 3.4a.8.8 0 0 0 .2.9l2 1.6a7.7 7.7 0 0 0-.1 1.4l-2 1.6a.8.8 0 0 0-.2.9l2 3.4a.8.8 0 0 0 .9.3l2.3-1a8.2 8.2 0 0 0 1.4.8l.4 2.5a.8.8 0 0 0 .8.7h4a.8.8 0 0 0 .8-.7l.4-2.5a8.2 8.2 0 0 0 1.4-.8l2.3 1a.8.8 0 0 0 .9-.3l2-3.4a.8.8 0 0 0-.2-.9Z" fill="none" stroke="currentColor" stroke-width="1.5" />',
    'filter'   => '<path d="M4 6h16M7 12h10M10 18h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />',
    'download' => '<path d="M12 3v10m0 0 4-4m-4 4-4-4M4 17h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>',
  ];

  // Logo file (opsional). Taruh logo di public/images/logo-helpdesk.svg
  $logoPath = asset('images/logo-helpdesk.svg');

  // Menu per role
  $menu = $role === 'IT'
    ? [
        ['label'=>'Dashboard',   'route'=>'it.dashboard',   'icon'=>'list',   'active'=>$is('it.dashboard')],
        ['label'=>'Tiket Saya',  'route'=>'it.my',          'icon'=>'user',   'active'=>$is('it.my')],
        ['label'=>'Statistik',   'route'=>'it.stats',       'icon'=>'chart',  'active'=>$is('it.stats')],
        // contoh tambahan: export/filter global (kalau ada)
        // ['label'=>'Export',   'route'=>'it.export',      'icon'=>'download','active'=>$is('it.export')],
      ]
    : [
        ['label'=>'Buat Tiket',  'route'=>'cabang.dashboard', 'icon'=>'plus',    'active'=>$is('cabang.dashboard')],
        ['label'=>'Tiket Saya',  'route'=>'cabang.tickets',   'icon'=>'folder',  'active'=>$is('cabang.tickets*')],
        
      ];

  // Tambahkan ikon 'folder' sederhana jika dipakai di atas
  $icons['folder'] = '<path d="M3 7h6l2 2h10v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z" stroke="currentColor" stroke-width="1.8" fill="none"/>';
@endphp

{{-- Header logo menu --}}
<div class="flex items-center gap-3 mb-3 px-2">
  <img src="{{ $logoPath }}" alt="Logo Helpdesk" class="h-8 w-8 object-contain" onerror="this.style.display='none'">
  <div>
    <div class="text-sm font-semibold text-gray-900 leading-tight">Helpdesk</div>
    <div class="text-xs text-gray-500 leading-tight">{{ $user->name ?? '-' }}</div>
  </div>
</div>

{{-- Garis --}}
<hr class="border-gray-100 mb-3">

{{-- List menu --}}
<nav class="space-y-1">
  @foreach ($menu as $item)
    @php
      $active = $item['active'];
      $base   = 'group flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors';
      $cls    = $active
        ? 'bg-indigo-50 text-indigo-700'
        : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900';
    @endphp

    <a href="{{ route($item['route']) }}" class="{{ $base }} {{ $cls }}" aria-current="{{ $active ? 'page' : 'false' }}">
      <svg viewBox="0 0 24 24" class="h-5 w-5 shrink-0">{!! $icons[$item['icon']] ?? '' !!}</svg>
      <span class="text-sm font-medium truncate">{{ $item['label'] }}</span>
    </a>
  @endforeach
</nav>

{{-- Footer kecil (opsional) --}}
<div class="mt-4 px-3">
  <div class="text-[11px] text-gray-400">
    © {{ date('Y') }} Helpdesk.
  </div>
</div>
