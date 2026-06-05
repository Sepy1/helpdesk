@extends('layouts.app')
@section('title','Parameter')

@section('content')
@php
  $categories = $categories ?? collect();
  $rootCauses = $rootCauses ?? collect();
  $vendors = $vendors ?? collect();
  $its = $its ?? collect();
  $usersForAiChat = $usersForAiChat ?? collect();

  $categoryCount = $categories->count();
  $subcategoryCount = $categories->sum(fn ($category) => $category->subcategories->count());
  $rootCauseCount = $rootCauses->count();
  $rootCauseDetailCount = $rootCauses->sum(fn ($rootCause) => $rootCause->details->count());
  $visibleItCount = $its->where('visible_on_assign', true)->count();
  $aiUserCount = $usersForAiChat->where('ai_chat_enabled', true)->count();

  $card = 'rounded-lg border border-slate-200 bg-white shadow-sm';
  $cardHead = 'border-b border-slate-100 px-4 py-3 sm:px-5';
  $cardTitle = 'text-base font-semibold text-slate-900';
  $cardHint = 'mt-1 text-xs leading-5 text-slate-500';
  $input = 'h-10 w-full rounded-lg border-slate-200 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500';
  $btnPrimary = 'inline-flex h-10 shrink-0 items-center justify-center rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:cursor-not-allowed disabled:opacity-60';
  $btnSecondary = 'inline-flex h-10 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500';
  $btnDanger = 'inline-flex items-center rounded-md px-2 py-1 text-sm font-medium text-red-600 hover:bg-red-50 hover:text-red-700';
  $tableWrap = 'max-h-72 overflow-auto';
  $th = 'whitespace-nowrap px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500';
  $td = 'px-4 py-2.5 text-sm text-slate-700';
@endphp

<div class="w-full max-w-none pb-8">
  <div class="space-y-4">
    <section class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
      <div class="min-w-0">
        <div class="flex flex-wrap items-center gap-2 text-xs font-medium text-slate-500">
          <span class="rounded-md border border-blue-100 bg-blue-50 px-2 py-1 text-blue-700">IT Parameter</span>
          <span>{{ now()->format('d/m/Y H:i') }}</span>
        </div>
        <h1 class="mt-2 text-2xl font-semibold tracking-normal text-slate-900 sm:text-3xl">Parameter</h1>
        <p class="mt-1 max-w-3xl text-sm text-slate-500">Kelola master tiket, penutupan, vendor, penugasan IT, dan akses AI chat.</p>
      </div>

      <div class="flex flex-wrap gap-2">
        <a href="{{ route('it.users.index') }}" class="{{ $btnSecondary }}">Kelola user</a>
        <a href="{{ route('it.dashboard') }}" class="{{ $btnPrimary }}">Lihat tiket</a>
      </div>
    </section>

    @if(session('success'))
      <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm" role="status">
        {{ session('success') }}
      </div>
    @endif
    @if(session('error'))
      <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-sm" role="alert">
        {{ session('error') }}
      </div>
    @endif

    <section class="grid grid-cols-2 gap-3 md:grid-cols-4 xl:grid-cols-6">
      <div class="{{ $card }} p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Kategori</p>
        <p class="mt-2 text-2xl font-semibold text-slate-950">{{ $categoryCount }}</p>
      </div>
      <div class="{{ $card }} p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Subkategori</p>
        <p class="mt-2 text-2xl font-semibold text-slate-950">{{ $subcategoryCount }}</p>
      </div>
      <div class="{{ $card }} p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Root cause</p>
        <p class="mt-2 text-2xl font-semibold text-slate-950">{{ $rootCauseCount }}</p>
      </div>
      <div class="{{ $card }} p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Detail root</p>
        <p class="mt-2 text-2xl font-semibold text-slate-950">{{ $rootCauseDetailCount }}</p>
      </div>
      <div class="{{ $card }} p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Vendor</p>
        <p class="mt-2 text-2xl font-semibold text-slate-950">{{ $vendors->count() }}</p>
      </div>
      <div class="{{ $card }} p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">IT tampil</p>
        <p class="mt-2 text-2xl font-semibold text-slate-950">{{ $visibleItCount }}</p>
      </div>
    </section>

    <section class="grid grid-cols-1 gap-4 xl:grid-cols-3">
      <article class="{{ $card }}">
        <div class="{{ $cardHead }}">
          <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
            <div>
              <h2 class="{{ $cardTitle }}">Kategori</h2>
              <p class="{{ $cardHint }}">Master kategori tiket yang dipilih saat tiket dibuat.</p>
            </div>
            <form method="POST" action="{{ route('it.parameters.category.store') }}" class="flex w-full flex-col gap-2 sm:flex-row lg:max-w-md">
              @csrf
              <input name="name" required class="{{ $input }}" placeholder="Nama kategori">
              <button type="submit" class="{{ $btnPrimary }}">Tambah</button>
            </form>
          </div>
        </div>
        <div class="{{ $tableWrap }}">
          <table class="min-w-full divide-y divide-slate-100">
            <thead class="sticky top-0 z-10 bg-slate-50">
              <tr>
                <th class="{{ $th }} w-12">#</th>
                <th class="{{ $th }}">Nama</th>
                <th class="{{ $th }} w-20 text-right">Sub</th>
                <th class="{{ $th }} w-24 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
              @forelse($categories as $i => $category)
                <tr class="hover:bg-slate-50">
                  <td class="{{ $td }} text-slate-500">{{ $i + 1 }}</td>
                  <td class="{{ $td }} font-medium text-slate-900">{{ $category->name }}</td>
                  <td class="{{ $td }} text-right tabular-nums">{{ $category->subcategories->count() }}</td>
                  <td class="{{ $td }} text-right">
                    <form method="POST" action="{{ route('it.parameters.category.delete', $category->id) }}" onsubmit="return confirm('Hapus kategori?');" class="inline">
                      @csrf
                      <button type="submit" class="{{ $btnDanger }}">Hapus</button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada kategori.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </article>

      <article class="{{ $card }}">
        <div class="{{ $cardHead }}">
          <h2 class="{{ $cardTitle }}">Subkategori</h2>
          <p class="{{ $cardHint }}">Tautkan subkategori ke kategori induk.</p>
          <form method="POST" action="{{ route('it.parameters.subcategory.store') }}" class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-[12rem_minmax(0,1fr)_auto]">
            @csrf
            <select name="category_id" required class="{{ $input }}">
              <option value="">Kategori</option>
              @foreach($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
              @endforeach
            </select>
            <input name="name" required class="{{ $input }}" placeholder="Nama subkategori">
            <button type="submit" class="{{ $btnPrimary }}">Tambah</button>
          </form>
        </div>
        <div class="{{ $tableWrap }}">
          <table class="min-w-full divide-y divide-slate-100">
            <thead class="sticky top-0 z-10 bg-slate-50">
              <tr>
                <th class="{{ $th }} w-12">#</th>
                <th class="{{ $th }}">Subkategori</th>
                <th class="{{ $th }}">Kategori</th>
                <th class="{{ $th }} w-24 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
              @php $subcategoryIndex = 0; @endphp
              @foreach($categories as $category)
                @foreach($category->subcategories as $subcategory)
                  @php $subcategoryIndex++; @endphp
                  <tr class="hover:bg-slate-50">
                    <td class="{{ $td }} text-slate-500">{{ $subcategoryIndex }}</td>
                    <td class="{{ $td }} font-medium text-slate-900">{{ $subcategory->name }}</td>
                    <td class="{{ $td }}">{{ $category->name }}</td>
                    <td class="{{ $td }} text-right">
                      <form method="POST" action="{{ route('it.parameters.subcategory.delete', $subcategory->id) }}" onsubmit="return confirm('Hapus subkategori?');" class="inline">
                        @csrf
                        <button type="submit" class="{{ $btnDanger }}">Hapus</button>
                      </form>
                    </td>
                  </tr>
                @endforeach
              @endforeach
              @if($subcategoryIndex === 0)
                <tr>
                  <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada subkategori.</td>
                </tr>
              @endif
            </tbody>
          </table>
        </div>
      </article>

      <article class="{{ $card }}">
        <div class="{{ $cardHead }}">
          <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
            <div>
              <h2 class="{{ $cardTitle }}">Root cause</h2>
              <p class="{{ $cardHint }}">Master penyebab saat tiket ditutup.</p>
            </div>
            <form method="POST" action="{{ route('it.parameters.rootcause.store') }}" class="flex w-full flex-col gap-2 sm:flex-row lg:max-w-md">
              @csrf
              <input name="name" required class="{{ $input }}" placeholder="Nama root cause">
              <button type="submit" class="{{ $btnPrimary }}">Tambah</button>
            </form>
          </div>
        </div>
        <div class="{{ $tableWrap }}">
          <table class="min-w-full divide-y divide-slate-100">
            <thead class="sticky top-0 z-10 bg-slate-50">
              <tr>
                <th class="{{ $th }} w-12">#</th>
                <th class="{{ $th }}">Nama</th>
                <th class="{{ $th }} w-20 text-right">Detail</th>
                <th class="{{ $th }} w-24 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
              @forelse($rootCauses as $i => $rootCause)
                <tr class="hover:bg-slate-50">
                  <td class="{{ $td }} text-slate-500">{{ $i + 1 }}</td>
                  <td class="{{ $td }} font-medium text-slate-900">{{ $rootCause->name }}</td>
                  <td class="{{ $td }} text-right tabular-nums">{{ $rootCause->details->count() }}</td>
                  <td class="{{ $td }} text-right">
                    <form method="POST" action="{{ route('it.parameters.rootcause.delete', $rootCause->id) }}" onsubmit="return confirm('Hapus root cause beserta semua detailnya?');" class="inline">
                      @csrf
                      <button type="submit" class="{{ $btnDanger }}">Hapus</button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada root cause.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </article>
    </section>

    <section class="grid grid-cols-1 gap-4 xl:grid-cols-3">
      <article class="{{ $card }} xl:col-span-2">
        <div class="{{ $cardHead }}">
          <h2 class="{{ $cardTitle }}">Detail root cause</h2>
          <p class="{{ $cardHint }}">Detail akan muncul sebagai pilihan radio saat IT menutup tiket.</p>
          <form method="POST" action="{{ route('it.parameters.rootcause.detail.store') }}" class="mt-3 grid grid-cols-1 gap-2 lg:grid-cols-[12rem_minmax(0,1fr)_auto_auto] lg:items-center">
            @csrf
            <select name="root_cause_id" required class="{{ $input }}" @disabled($rootCauseCount === 0)>
              <option value="">Root cause</option>
              @foreach($rootCauses as $rootCause)
                <option value="{{ $rootCause->id }}">{{ $rootCause->name }}</option>
              @endforeach
            </select>
            <input name="label" required maxlength="191" class="{{ $input }}" placeholder="Nama detail root cause" @disabled($rootCauseCount === 0)>
            <label class="inline-flex h-10 items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700">
              <input type="checkbox" name="is_other" value="1" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" @disabled($rootCauseCount === 0)>
              <span>Lainnya</span>
            </label>
            <button type="submit" class="{{ $btnPrimary }}" @disabled($rootCauseCount === 0)>Tambah</button>
          </form>
          <p class="mt-2 text-xs text-slate-500">Opsi Lainnya membuat closed note wajib di form close ticket.</p>
        </div>
        <div class="{{ $tableWrap }}">
          <table class="min-w-full divide-y divide-slate-100">
            <thead class="sticky top-0 z-10 bg-slate-50">
              <tr>
                <th class="{{ $th }} w-12">#</th>
                <th class="{{ $th }}">Detail</th>
                <th class="{{ $th }}">Root cause</th>
                <th class="{{ $th }} w-24 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
              @php $detailIndex = 0; @endphp
              @foreach($rootCauses as $rootCause)
                @foreach($rootCause->details as $detail)
                  @php $detailIndex++; @endphp
                  <tr class="hover:bg-slate-50">
                    <td class="{{ $td }} text-slate-500">{{ $detailIndex }}</td>
                    <td class="{{ $td }} font-medium text-slate-900">
                      {{ $detail->label }}
                      @if($detail->is_other)
                        <span class="ml-2 rounded-md border border-amber-200 bg-amber-50 px-1.5 py-0.5 text-[11px] font-semibold text-amber-700">Lainnya</span>
                      @endif
                    </td>
                    <td class="{{ $td }}">{{ $rootCause->name }}</td>
                    <td class="{{ $td }} text-right">
                      <form method="POST" action="{{ route('it.parameters.rootcause.detail.delete', $detail) }}" onsubmit="return confirm('Hapus detail ini?');" class="inline">
                        @csrf
                        <button type="submit" class="{{ $btnDanger }}">Hapus</button>
                      </form>
                    </td>
                  </tr>
                @endforeach
              @endforeach
              @if($rootCauseCount === 0)
                <tr>
                  <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500">Tambah root cause terlebih dahulu.</td>
                </tr>
              @elseif($detailIndex === 0)
                <tr>
                  <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada detail root cause.</td>
                </tr>
              @endif
            </tbody>
          </table>
        </div>
      </article>

      <article class="{{ $card }}">
        <div class="{{ $cardHead }}">
          <div class="flex items-start justify-between gap-3">
            <div>
              <h2 class="{{ $cardTitle }}">Vendor</h2>
              <p class="{{ $cardHint }}">Daftar akun vendor untuk eskalasi tiket.</p>
            </div>
            <a href="{{ route('it.users.index') }}" class="{{ $btnSecondary }}">Kelola</a>
          </div>
        </div>
        <div class="{{ $tableWrap }}">
          <table class="min-w-full divide-y divide-slate-100">
            <thead class="sticky top-0 z-10 bg-slate-50">
              <tr>
                <th class="{{ $th }} w-12">#</th>
                <th class="{{ $th }}">Nama</th>
                <th class="{{ $th }}">Email</th>
                <th class="{{ $th }} w-20 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
              @forelse($vendors as $i => $vendor)
                <tr class="hover:bg-slate-50">
                  <td class="{{ $td }} text-slate-500">{{ $i + 1 }}</td>
                  <td class="{{ $td }} font-medium text-slate-900">{{ $vendor->name }}</td>
                  <td class="{{ $td }} break-all">{{ $vendor->email }}</td>
                  <td class="{{ $td }} text-right">
                    <a href="{{ route('it.users.edit', $vendor->id) }}" class="inline-flex items-center rounded-md px-2 py-1 text-sm font-medium text-blue-600 hover:bg-blue-50 hover:text-blue-700">Edit</a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada vendor.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </article>
    </section>

    <section class="grid grid-cols-1 gap-4 xl:grid-cols-2">
      <article class="{{ $card }}">
        <form method="POST" action="{{ route('it.parameters.it.visibility') }}">
          @csrf
          <div class="{{ $cardHead }}">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
              <div>
                <h2 class="{{ $cardTitle }}">Ditugaskan ke IT</h2>
                <p class="{{ $cardHint }}">Pilih user IT yang tampil pada dropdown penugasan.</p>
              </div>
              <div class="flex flex-wrap gap-2">
                <a href="{{ route('it.users.index') }}" class="{{ $btnSecondary }}">Kelola IT</a>
                <button type="submit" class="{{ $btnPrimary }}">Simpan</button>
              </div>
            </div>
          </div>
          <div class="{{ $tableWrap }}">
            <table class="min-w-full divide-y divide-slate-100">
              <thead class="sticky top-0 z-10 bg-slate-50">
                <tr>
                  <th class="{{ $th }} w-12">#</th>
                  <th class="{{ $th }}">Nama</th>
                  <th class="{{ $th }}">Email</th>
                  <th class="{{ $th }} w-28 text-center">Tampil</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 bg-white">
                @forelse($its as $i => $it)
                  <tr class="hover:bg-slate-50">
                    <td class="{{ $td }} text-slate-500">{{ $i + 1 }}</td>
                    <td class="{{ $td }} font-medium text-slate-900">{{ $it->name }}</td>
                    <td class="{{ $td }} break-all">{{ $it->email }}</td>
                    <td class="{{ $td }} text-center">
                      <input type="checkbox" name="visible[]" value="{{ $it->id }}" @checked($it->visible_on_assign) class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada user IT.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </form>
      </article>

      <article class="{{ $card }}">
        <form method="POST" action="{{ route('it.parameters.ai_chat') }}">
          @csrf
          <div class="{{ $cardHead }}">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
              <div>
                <h2 class="{{ $cardTitle }}">AI Chat Assistant</h2>
                <p class="{{ $cardHint }}">{{ $aiUserCount }} user diizinkan menggunakan AI chat.</p>
              </div>
              <button type="submit" class="{{ $btnPrimary }}">Simpan</button>
            </div>

            <label class="mt-3 flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-3">
              <input type="hidden" name="ai_chat_enabled" value="0">
              <input type="checkbox" name="ai_chat_enabled" value="1" @checked($aiChatEnabled ?? true) class="mt-0.5 h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
              <span class="text-sm text-slate-700">
                Tampilkan floating AI chat.
                <span class="block text-xs text-slate-500">Jika dimatikan, tombol AI disembunyikan dan endpoint chat menolak request.</span>
              </span>
            </label>
          </div>
          <div class="{{ $tableWrap }}">
            <table class="min-w-full divide-y divide-slate-100">
              <thead class="sticky top-0 z-10 bg-slate-50">
                <tr>
                  <th class="{{ $th }} w-12">#</th>
                  <th class="{{ $th }}">Nama</th>
                  <th class="{{ $th }}">Email</th>
                  <th class="{{ $th }} w-24">Role</th>
                  <th class="{{ $th }} w-28 text-center">Enable</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 bg-white">
                @forelse($usersForAiChat as $i => $user)
                  <tr class="hover:bg-slate-50">
                    <td class="{{ $td }} text-slate-500">{{ $i + 1 }}</td>
                    <td class="{{ $td }} font-medium text-slate-900">{{ $user->name }}</td>
                    <td class="{{ $td }} break-all">{{ $user->email }}</td>
                    <td class="{{ $td }}">{{ $user->role }}</td>
                    <td class="{{ $td }} text-center">
                      <input type="checkbox" name="ai_chat_users[]" value="{{ $user->id }}" @checked($user->ai_chat_enabled) class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada user.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </form>
      </article>
    </section>
  </div>
</div>
@endsection
