@php
  use Illuminate\Support\Facades\Route;

  $role = auth()->user()?->role;
  $isIT = $role === 'IT';
  $isVendor = $role === 'VENDOR';

  $base = 'flex flex-col items-center justify-center gap-1 px-6 py-2 text-xs';
  $on   = 'text-tulisan-100';
  $off  = 'text-tulisan-500';

  // jumlah kolom: IT = 4 (Dashboard, Tiket, User, Profil), CABANG = 3 (Buat, Tiket, Profil), VENDOR = 2 (Tiket, Profil)
  $cols = $isIT ? 'grid-cols-4' : ($isVendor ? 'grid-cols-2' : 'grid-cols-3');
@endphp

{{-- BOTTOM NAV: tampil di mobile, gradient biru, tinggi jelas --}}
<nav class="fixed inset-x-0 bottom-0 z-50 md:hidden
            h-14 sm:h-16
            bg-gradient-to-r from-blue-700 via-indigo-600 to-violet-600
            border-t border-white/10 shadow-lg"
     style="padding-bottom: env(safe-area-inset-bottom)">
  <ul class="grid {{ $cols }} text-center h-full">
    {{-- LEFT ITEM (hidden for Vendor) --}}
    @if(!$isVendor)
    <li class="h-full">
      @if($isIT)
        <a href="{{ route('it.dashboard') }}"
           class="{{ request()->routeIs('it.dashboard') ? "$base text-white" : "$base text-white/80 hover:bg-white/10" }} h-full">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M12 3 2 12h3v8h6v-6h2v6h6v-8h3z"/>
          </svg>
          <span>Dashboard</span>
        </a>
      @else
        @php
          $createUrl = Route::has('cabang.dashboard') ? route('cabang.dashboard') : url('/cabang/dashboard');
        @endphp
        <a href="{{ $createUrl }}"
           class="{{ request()->routeIs('cabang.dashboard') ? "$base text-white" : "$base text-white/80 hover:bg-white/10" }} h-full">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M4 3h16a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Zm7 4v4H7v2h4v4h2v-4h4v-2h-4V7Z"/>
          </svg>
          <span>Buat</span>
        </a>
      @endif
    </li>
    @endif

    {{-- CENTER ITEM: Tiket --}}
    <li class="h-full">
      @if($isIT)
        <a href="{{ route('it.my') }}"
           class="{{ request()->routeIs('it.my') ? "$base text-white" : "$base text-white/80 hover:bg-white/10" }} h-full">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M4 6h16v2H4V6Zm0 5h16v2H4v-2Zm0 5h16v2H4v-2Z"/>
          </svg>
          <span>Tiket</span>
        </a>
      @elseif(!$isVendor)
        <a href="{{ route('cabang.tickets') }}"
           class="{{ request()->routeIs('cabang.tickets') ? "$base text-white" : "$base text-white/80 hover:bg-white/10" }} h-full">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M4 6h16v2H4V6Zm0 5h16v2H4v-2Zm0 5h16v2H4v-2Z"/>
          </svg>
          <span>Tiket</span>
        </a>
      @else
        <a href="{{ route('vendor.tickets') }}"
           class="{{ request()->routeIs('vendor.tickets') ? "$base text-white" : "$base text-white/80 hover:bg-white/10" }} h-full">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M4 6h16v2H4V6Zm0 5h16v2H4v-2Zm0 5h16v2H4v-2Z"/>
          </svg>
          <span>Tiket</span>
        </a>
      @endif
    </li>
    {{-- ITEM: Profil (semua role) --}}
    <li class="h-full">
      <a href="{{ route('profile.edit') }}"
         class="{{ request()->routeIs('profile.edit') ? "$base text-white" : "$base text-white/80 hover:bg-white/10" }} h-full">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path d="M12 12a4.5 4.5 0 1 0-4.5-4.5A4.5 4.5 0 0 0 12 12Zm0 2c-4 0-7 2.2-7 5v1h14v-1c0-2.8-3-5-7-5Z" />
        </svg>
        <span>Profil</span>
      </a>
    </li>

    {{-- ITEM: IT Manajemen User --}}
    @if($isIT)
    <li class="h-full">
      <a href="{{ route('it.users.index') }}"
         class="{{ request()->routeIs('it.users.*') ? "$base text-white" : "$base text-white/80 hover:bg-white/10" }} h-full">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path d="M12 12a4.5 4.5 0 1 0-4.5-4.5A4.5 4.5 0 0 0 12 12Zm0 2c-4 0-7 2.2-7 5v1h14v-1c0-2.8-3-5-7-5Z" />
        </svg>
        <span>User</span>
      </a>
    </li>
    @endif

    {{-- ITEM: Statistik (IT) --}}
    @if($isIT)
    <li class="h-full">
      <a href="{{ route('it.stats') }}"
         class="{{ request()->routeIs('it.stats') ? "$base text-white" : "$base text-white/80 hover:bg-white/10" }} h-full">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path d="M11 2a1 1 0 0 1 1-1 12 12 0 1 1-12 12 1 1 0 0 1 1-1h10V2Z"/>
          <path d="M13 2v9h9A9 9 0 0 0 13 2Z"/>
        </svg>
        <span>Statistik</span>
      </a>
    </li>
    @endif
  </ul>
</nav>
