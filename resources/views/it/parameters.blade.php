@extends('layouts.app')
@section('title','Parameter')

@section('content')
<div class="w-full max-w-none p-3 sm:p-5 text-xs sm:text-sm space-y-6">
  <div>
    <h1 class="text-xl sm:text-2xl font-semibold text-gray-900 tracking-tight">Parameter</h1>
    <p class="mt-1 text-sm text-gray-500">Kelola kategori, subkategori, root cause, detail root cause (penutupan), vendor, dan visibilitas IT pada penugasan.</p>
  </div>

  @if(session('success'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm" role="status">
      {{ session('success') }}
    </div>
  @endif
  @if(session('error'))
    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-sm" role="alert">
      {{ session('error') }}
    </div>
  @endif

  @php
    $card = 'rounded-2xl bg-white shadow-md ring-1 ring-gray-100 min-w-0';
    $cardHead = 'flex flex-col gap-4 border-b border-gray-100 bg-gradient-to-r from-gray-50/80 to-white px-4 py-4 sm:px-5 sm:flex-row sm:items-center sm:justify-between';
    $cardTitle = 'text-base font-semibold text-gray-900';
    $input = 'w-full min-w-0 rounded-lg border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500';
    $btnPrimary = 'inline-flex shrink-0 items-center justify-center rounded-lg bg-gradient-to-r from-blue-500 to-sky-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:from-blue-600 hover:to-sky-600';
    $btnSecondary = 'inline-flex shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50';
    $tableWrap = 'max-h-56 overflow-x-auto overflow-y-auto sm:max-h-64';
    $th = 'px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500';
    $td = 'px-4 py-2.5 text-sm text-gray-800';
  @endphp

  <div class="grid min-w-0 grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
    {{-- Kategori --}}
    <section class="{{ $card }}">
      <div class="{{ $cardHead }}">
        <div>
          <h2 class="{{ $cardTitle }}">Kategori</h2>
          <p class="mt-0.5 text-xs text-gray-500">Master kategori tiket.</p>
        </div>
        <form method="POST" action="{{ route('it.parameters.category.store') }}" class="flex w-full flex-col gap-2 sm:w-auto sm:max-w-md sm:flex-row sm:items-center">
          @csrf
          <input name="name" required class="{{ $input }} sm:flex-1" placeholder="Nama kategori" />
          <button type="submit" class="{{ $btnPrimary }}">Tambah</button>
        </form>
      </div>
      <div class="{{ $tableWrap }}">
        <table class="min-w-full divide-y divide-gray-100 text-sm">
          <thead class="sticky top-0 z-10 bg-gray-50/95 backdrop-blur-sm">
            <tr>
              <th class="{{ $th }} w-10">#</th>
              <th class="{{ $th }}">Nama</th>
              <th class="{{ $th }} w-24 text-right sm:text-left">Sub</th>
              <th class="{{ $th }} w-24">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 bg-white">
            @foreach($categories as $i => $c)
              <tr class="transition-colors hover:bg-gray-50/80">
                <td class="{{ $td }} text-gray-500">{{ $i + 1 }}</td>
                <td class="{{ $td }} font-medium text-gray-900">{{ $c->name }}</td>
                <td class="{{ $td }} text-right tabular-nums text-gray-600 sm:text-left">{{ $c->subcategories->count() }}</td>
                <td class="{{ $td }}">
                  <form method="POST" action="{{ route('it.parameters.category.delete', $c->id) }}" onsubmit="return confirm('Hapus kategori?');" class="inline">
                    @csrf
                    <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-700 hover:underline">Hapus</button>
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </section>

    {{-- Subkategori: judul + form bertumpuk agar tombol tidak terpotong di grid sempit --}}
    <section class="{{ $card }}">
      <div class="space-y-4 border-b border-gray-100 bg-gradient-to-r from-gray-50/80 to-white px-4 py-4 sm:px-5">
        <div>
          <h2 class="{{ $cardTitle }}">Subkategori</h2>
          <p class="mt-0.5 text-xs text-gray-500">Tautkan ke kategori induk.</p>
        </div>
        <form method="POST" action="{{ route('it.parameters.subcategory.store') }}" class="flex w-full min-w-0 flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-stretch sm:gap-x-2 sm:gap-y-2">
          @csrf
          <select name="category_id" required class="{{ $input }} w-full shrink-0 sm:w-44">
            <option value="">Kategori</option>
            @foreach($categories as $c)
              <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
          </select>
          <input name="name" required class="{{ $input }} w-full min-w-0 sm:min-w-[10rem] sm:flex-1" placeholder="Nama subkategori" />
          <button type="submit" class="{{ $btnPrimary }} w-full shrink-0 sm:w-auto sm:self-center">Tambah</button>
        </form>
      </div>
      <div class="{{ $tableWrap }}">
        <table class="min-w-full divide-y divide-gray-100 text-sm">
          <thead class="sticky top-0 z-10 bg-gray-50/95 backdrop-blur-sm">
            <tr>
              <th class="{{ $th }} w-10">#</th>
              <th class="{{ $th }}">Subkategori</th>
              <th class="{{ $th }}">Kategori</th>
              <th class="{{ $th }} w-24">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 bg-white">
            @php $count = 0; @endphp
            @foreach($categories as $c)
              @foreach($c->subcategories as $s)
                @php $count++; @endphp
                <tr class="transition-colors hover:bg-gray-50/80">
                  <td class="{{ $td }} text-gray-500">{{ $count }}</td>
                  <td class="{{ $td }}">{{ $s->name }}</td>
                  <td class="{{ $td }} text-gray-600">{{ $c->name }}</td>
                  <td class="{{ $td }}">
                    <form method="POST" action="{{ route('it.parameters.subcategory.delete', $s->id) }}" onsubmit="return confirm('Hapus subkategori?');" class="inline">
                      @csrf
                      <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-700 hover:underline">Hapus</button>
                    </form>
                  </td>
                </tr>
              @endforeach
            @endforeach
            @if($count === 0)
              <tr>
                <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada subkategori</td>
              </tr>
            @endif
          </tbody>
        </table>
      </div>
    </section>

    {{-- Root cause (hanya master nama) --}}
    <section class="{{ $card }}">
      <div class="{{ $cardHead }}">
        <div>
          <h2 class="{{ $cardTitle }}">Root cause</h2>
          <p class="mt-0.5 text-xs text-gray-500">Master analisis / penutupan. Daftar detail per root cause ada di card <span class="font-medium text-gray-700">Detail root cause</span>.</p>
        </div>
        <form method="POST" action="{{ route('it.parameters.rootcause.store') }}" class="flex w-full flex-col gap-2 sm:w-auto sm:max-w-md sm:flex-row sm:items-center">
          @csrf
          <input name="name" required class="{{ $input }} sm:flex-1" placeholder="Nama root cause" />
          <button type="submit" class="{{ $btnPrimary }}">Tambah</button>
        </form>
      </div>
      <div class="{{ $tableWrap }}">
        <table class="min-w-full divide-y divide-gray-100 text-sm">
          <thead class="sticky top-0 z-10 bg-gray-50/95 backdrop-blur-sm">
            <tr>
              <th class="{{ $th }} w-10">#</th>
              <th class="{{ $th }}">Nama</th>
              <th class="{{ $th }} w-16 text-center tabular-nums" title="Jumlah detail">Det.</th>
              <th class="{{ $th }} w-24">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 bg-white">
            @foreach($rootCauses as $i => $rc)
              <tr class="transition-colors hover:bg-gray-50/80">
                <td class="{{ $td }} text-gray-500">{{ $i + 1 }}</td>
                <td class="{{ $td }} font-medium text-gray-900">{{ $rc->name }}</td>
                <td class="{{ $td }} text-center text-gray-600 tabular-nums">{{ $rc->details->count() }}</td>
                <td class="{{ $td }}">
                  <form method="POST" action="{{ route('it.parameters.rootcause.delete', $rc->id) }}" onsubmit="return confirm('Hapus root cause beserta semua detailnya?');" class="inline">
                    @csrf
                    <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-700 hover:underline">Hapus</button>
                  </form>
                </td>
              </tr>
            @endforeach
            @if(count($rootCauses) === 0)
              <tr>
                <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada root cause</td>
              </tr>
            @endif
          </tbody>
        </table>
      </div>
    </section>

    {{-- Satu baris: Detail root cause | Vendor | Ditugaskan ke (IT) --}}
    <div class="min-w-0 md:col-span-2 lg:col-span-3 grid grid-cols-1 gap-6 md:grid-cols-3">
    {{-- Detail root cause (sama pola layout dengan Subkategori) --}}
    <section class="{{ $card }}">
      <div class="space-y-4 border-b border-gray-100 bg-gradient-to-r from-gray-50/80 to-white px-4 py-4 sm:px-5">
        <div>
          <h2 class="{{ $cardTitle }}">Detail root cause</h2>
          <p class="mt-0.5 text-xs text-gray-500">Tautkan ke root cause induk. Dipakai sebagai radio saat IT menutup tiket; <span class="font-medium text-gray-800">closed note</span> mengikuti form tutup tiket.</p>
        </div>
        <form method="POST" action="{{ route('it.parameters.rootcause.detail.store') }}" class="flex w-full min-w-0 flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-stretch sm:gap-x-2 sm:gap-y-2">
          @csrf
          <select name="root_cause_id" required class="{{ $input }} w-full shrink-0 sm:w-44" @if(count($rootCauses) === 0) disabled @endif>
            <option value="">Root cause</option>
            @foreach($rootCauses as $rc)
              <option value="{{ $rc->id }}">{{ $rc->name }}</option>
            @endforeach
          </select>
          <input name="label" required maxlength="191" class="{{ $input }} w-full min-w-0 sm:min-w-[10rem] sm:flex-1" placeholder="Nama detail (label radio)" @if(count($rootCauses) === 0) disabled @endif />
          <label class="inline-flex w-full min-w-0 cursor-pointer items-center gap-2 rounded-lg border border-transparent px-0 py-1 text-xs text-gray-700 sm:w-auto sm:shrink-0 sm:self-center sm:border-0 sm:px-1">
            <input type="checkbox" name="is_other" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" @if(count($rootCauses) === 0) disabled @endif />
            <span class="leading-snug">Lainnya → closed note wajib</span>
          </label>
          <button type="submit" class="{{ $btnPrimary }} w-full shrink-0 sm:w-auto sm:self-center" @if(count($rootCauses) === 0) disabled @endif>Tambah</button>
        </form>
      </div>
      <div class="{{ $tableWrap }}">
        <table class="min-w-full divide-y divide-gray-100 text-sm">
          <thead class="sticky top-0 z-10 bg-gray-50/95 backdrop-blur-sm">
            <tr>
              <th class="{{ $th }} w-10">#</th>
              <th class="{{ $th }}">Detail</th>
              <th class="{{ $th }}">Root cause</th>
              <th class="{{ $th }} w-24">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 bg-white">
            @php $detailCount = 0; @endphp
            @foreach($rootCauses as $rc)
              @foreach($rc->details as $d)
                @php $detailCount++; @endphp
                <tr class="transition-colors hover:bg-gray-50/80">
                  <td class="{{ $td }} text-gray-500">{{ $detailCount }}</td>
                  <td class="{{ $td }}">
                    <span class="text-gray-800">{{ $d->label }}</span>
                    @if($d->is_other)
                      <span class="ml-1.5 align-middle rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-semibold text-amber-900">Lainnya</span>
                    @endif
                  </td>
                  <td class="{{ $td }} text-gray-600">{{ $rc->name }}</td>
                  <td class="{{ $td }}">
                    <form method="POST" action="{{ route('it.parameters.rootcause.detail.delete', $d) }}" onsubmit="return confirm('Hapus detail ini?');" class="inline">
                      @csrf
                      <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-700 hover:underline">Hapus</button>
                    </form>
                  </td>
                </tr>
              @endforeach
            @endforeach
            @if(count($rootCauses) === 0)
              <tr>
                <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">Tambah root cause terlebih dahulu.</td>
              </tr>
            @elseif($detailCount === 0)
              <tr>
                <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada detail — penutupan untuk root cause terkait hanya memakai closed note bebas.</td>
              </tr>
            @endif
          </tbody>
        </table>
      </div>
    </section>

    {{-- Vendor --}}
    <section class="{{ $card }}">
      <div class="{{ $cardHead }}">
        <div>
          <h2 class="{{ $cardTitle }}">Vendor</h2>
          <p class="mt-0.5 text-xs text-gray-500">Daftar akun vendor.</p>
        </div>
        <a href="{{ route('it.users.index') }}" class="{{ $btnPrimary }}">Kelola vendor</a>
      </div>
      <div class="{{ $tableWrap }}">
        <table class="min-w-full divide-y divide-gray-100 text-sm">
          <thead class="sticky top-0 z-10 bg-gray-50/95 backdrop-blur-sm">
            <tr>
              <th class="{{ $th }} w-10">#</th>
              <th class="{{ $th }}">Nama</th>
              <th class="{{ $th }} min-w-[10rem]">Email</th>
              <th class="{{ $th }} w-20">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 bg-white">
            @foreach($vendors as $i => $v)
              <tr class="transition-colors hover:bg-gray-50/80">
                <td class="{{ $td }} text-gray-500">{{ $i + 1 }}</td>
                <td class="{{ $td }} font-medium text-gray-900">{{ $v->name }}</td>
                <td class="{{ $td }} break-all text-gray-600">{{ $v->email }}</td>
                <td class="{{ $td }}">
                  <a href="{{ route('it.users.edit', $v->id) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 hover:underline">Edit</a>
                </td>
              </tr>
            @endforeach
            @if(count($vendors) === 0)
              <tr>
                <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada vendor</td>
              </tr>
            @endif
          </tbody>
        </table>
      </div>
    </section>

    {{-- Ditugaskan ke IT (sejajar Detail root cause + Vendor, lihat wrapper di atas) --}}
    <section class="{{ $card }}">
      <form method="POST" action="{{ route('it.parameters.it.visibility') }}">
        @csrf
        <div class="{{ $cardHead }}">
          <div>
            <h2 class="{{ $cardTitle }}">Ditugaskan ke (IT)</h2>
            <p class="mt-0.5 text-xs text-gray-500">Pilih user IT yang muncul di dropdown penugasan.</p>
          </div>
          <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center">
            <a href="{{ route('it.users.index') }}" class="{{ $btnSecondary }} text-center">Kelola IT</a>
            <button type="submit" class="{{ $btnPrimary }}">Simpan</button>
          </div>
        </div>
        <div class="{{ $tableWrap }} max-h-72 sm:max-h-80">
          <table class="min-w-full divide-y divide-gray-100 text-sm">
            <thead class="sticky top-0 z-10 bg-gray-50/95 backdrop-blur-sm">
              <tr>
                <th class="{{ $th }} w-10">#</th>
                <th class="{{ $th }}">Nama</th>
                <th class="{{ $th }} min-w-[10rem]">Email</th>
                <th class="{{ $th }} w-28">Tampilkan</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
              @foreach($its as $i => $it)
                <tr class="transition-colors hover:bg-gray-50/80">
                  <td class="{{ $td }} text-gray-500">{{ $i + 1 }}</td>
                  <td class="{{ $td }} font-medium text-gray-900">{{ $it->name }}</td>
                  <td class="{{ $td }} break-all text-gray-600">{{ $it->email }}</td>
                  <td class="{{ $td }}">
                    <input type="checkbox" name="visible[]" value="{{ $it->id }}" @checked($it->visible_on_assign)
                      class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                  </td>
                </tr>
              @endforeach
              @if(count($its) === 0)
                <tr>
                  <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada user IT</td>
                </tr>
              @endif
            </tbody>
          </table>
        </div>
      </form>
    </section>
    </div>
  </div>
</div>
@endsection
