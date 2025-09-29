@php
  use Illuminate\Support\Facades\Route;

  $role = auth()->user()?->role;
  $isIT = $role === 'IT';

  $base = 'flex flex-col items-center justify-center gap-1 px-6 py-2 text-xs';
  $on   = 'text-indigo-600';
  $off  = 'text-gray-600';

  // jumlah kolom: IT = 3, CABANG = 2
  $cols = $isIT ? 'grid-cols-3' : 'grid-cols-2';
@endphp

<nav class="md:hidden fixed bottom-0 left-0 right-0 z-50 bg-white border-t shadow-sm"
     style="padding-bottom: env(safe-area-inset-bottom)">
  <ul class="grid {{ $cols }} text-center">
    {{-- LEFT ITEM --}}
    <li>
      @if($isIT)
        {{-- IT: Dashboard (semua tiket) --}}
        <a href="{{ route('it.dashboard') }}"
           class="{{ request()->routeIs('it.dashboard') ? "$base $on" : "$base $off" }}">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 3 2 12h3v8h6v-6h2v6h6v-8h3z"/>
          </svg>
          <span>Dashboard</span>
        </a>
      @else
        {{-- Cabang: Buat (form ada di cabang.dashboard) --}}
        @php
          $createUrl = Route::has('cabang.dashboard')
                      ? route('cabang.dashboard')
                      : url('/cabang/dashboard'); // fallback
        @endphp
        <a href="{{ $createUrl }}"
           class="{{ request()->routeIs('cabang.dashboard') ? "$base $on" : "$base $off" }}">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
            <path d="M4 3h16a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Zm7 4v4H7v2h4v4h2v-4h4v-2h-4V7Z"/>
          </svg>
          <span>Buat</span>
        </a>
      @endif
    </li>

    {{-- CENTER ITEM: Tiket --}}
    <li>
      @if($isIT)
        {{-- IT: Tiket Saya --}}
        <a href="{{ route('it.my') }}"
           class="{{ request()->routeIs('it.my') ? "$base $on" : "$base $off" }}">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
            <path d="M4 6h16v2H4V6Zm0 5h16v2H4v-2Zm0 5h16v2H4v-2Z"/>
          </svg>
          <span>Tiket</span>
        </a>
      @else
        {{-- Cabang: Tiket Saya --}}
        <a href="{{ route('cabang.tickets') }}"
           class="{{ request()->routeIs('cabang.tickets') ? "$base $on" : "$base $off" }}">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
            <path d="M4 6h16v2H4V6Zm0 5h16v2H4v-2Zm0 5h16v2H4v-2Z"/>
          </svg>
          <span>Tiket</span>
        </a>
      @endif
    </li>

    {{-- RIGHT ITEM (hanya IT) --}}
    @if($isIT)
    <li>
      <a href="{{ route('it.stats') }}"
         class="{{ request()->routeIs('it.stats') ? "$base $on" : "$base $off" }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
          <path d="M11 2a1 1 0 0 1 1-1 12 12 0 1 1-12 12 1 1 0 0 1 1-1h10V2Z"/>
          <path d="M13 2v9h9A9 9 0 0 0 13 2Z"/>
        </svg>
        <span>Statistik</span>
      </a>
    </li>
    @endif
  </ul>
</nav>
