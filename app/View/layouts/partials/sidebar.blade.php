@php
  $isIT     = auth()->user()->role === 'IT';
  $isCabang = auth()->user()->role === 'CABANG';
@endphp

<aside x-data="{open:true}" class="hidden md:block w-64 shrink-0">
  <div class="h-[calc(100vh-64px)] sticky top-16 overflow-y-auto border-r bg-white">
    <div class="p-4">
      <div class="text-xs font-semibold text-gray-500 mb-2">MENU</div>

      {{-- IT --}}
      @if($isIT)
        <a href="{{ route('it.dashboard') }}"
           class="flex items-center gap-3 px-3 py-2 rounded-lg mb-1
                  {{ request()->routeIs('it.dashboard') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
          <span>📊</span> <span>Dashboard IT</span>
        </a>
      @endif

      {{-- CABANG --}}
      @if($isCabang)
        <a href="{{ route('cabang.dashboard') }}"
           class="flex items-center gap-3 px-3 py-2 rounded-lg mb-1
                  {{ request()->routeIs('cabang.dashboard') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
          <span>➕</span> <span>Buat Tiket</span>
        </a>
        <a href="{{ route('cabang.tickets') }}"
           class="flex items-center gap-3 px-3 py-2 rounded-lg mb-1
                  {{ request()->routeIs('cabang.tickets') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
          <span>🗂️</span> <span>Tiket Saya</span>
        </a>
      @endif

      {{-- Umum --}}
      <div class="mt-4 text-xs font-semibold text-gray-500 mb-2">LAINNYA</div>
      <a href="{{ route('profile.edit') }}"
         class="flex items-center gap-3 px-3 py-2 rounded-lg mb-1
                {{ request()->routeIs('profile.edit') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
        <span>👤</span> <span>Profil</span>
      </a>
    </div>
  </div>
</aside>
