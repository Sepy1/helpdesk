@extends('layouts.app')
@section('title','Parameter')

@section('content')
<div class="max-w-6xl mx-auto p-3 sm:p-5 text-xs sm:text-sm">
  <h1 class="text-2xl font-semibold mb-4">Parameter</h1>

  @if(session('success'))
    <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800">{{ session('success') }}</div>
  @endif

  <div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      {{-- Kategori --}}
      <div class="bg-white rounded shadow overflow-hidden">
        <div class="flex items-center justify-between p-4 border-b">
          <div class="text-lg font-medium">Kategori</div>
          <form method="POST" action="{{ route('it.parameters.category.store') }}" class="flex flex-col sm:flex-row items-center gap-2">
            @csrf
            <input name="name" required class="rounded border px-3 py-1 text-sm w-full sm:w-auto sm:flex-1 min-w-0" placeholder="Nama kategori" />
            <button class="px-3 py-1 bg-sky-600 text-white rounded text-sm">Tambah</button>
          </form>
        </div>

        <div class="p-4 overflow-x-auto max-h-52 overflow-y-auto">
          <table class="w-full text-sm">
            <thead class="text-left text-xs text-gray-500 border-b">
              <tr>
                <th class="py-2">#</th>
                <th class="py-2">Nama Kategori</th>
                <th class="py-2">Jumlah Subkategori</th>
                <th class="py-2">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y">
              @foreach($categories as $i => $c)
                <tr class="hover:bg-gray-50">
                  <td class="py-2">{{ $i + 1 }}</td>
                  <td class="py-2 font-medium max-w-xs truncate">{{ $c->name }}</td>
                  <td class="py-2">{{ $c->subcategories->count() }}</td>
                  <td class="py-2">
                    <div class="flex gap-2">
                      <form method="POST" action="{{ route('it.parameters.category.delete', $c->id) }}" onsubmit="return confirm('Hapus kategori?');">
                        @csrf
                        <button class="text-red-600 text-sm">Hapus</button>
                      </form>
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>

      {{-- Subkategori --}}
      <div class="bg-white rounded shadow overflow-hidden">
        <div class="flex items-center justify-between p-4 border-b">
          <div class="text-lg font-medium">Sub</div>
          <form method="POST" action="{{ route('it.parameters.subcategory.store') }}" class="flex flex-col sm:flex-row items-center gap-2">
            @csrf
            <select name="category_id" required class="rounded border px-3 py-1 text-sm w-full sm:w-auto sm:flex-1 min-w-0">
              <option value="">Pilih Kategori</option>
              @foreach($categories as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
              @endforeach
            </select>
            <input name="name" required class="rounded border px-3 py-1 text-sm w-full sm:w-auto sm:flex-1 min-w-0" placeholder="Nama subkategori" />
            <button class="px-3 py-1 bg-sky-600 text-white rounded text-sm">Tambah</button>
          </form>
        </div>

        <div class="p-4 overflow-x-auto max-h-52 overflow-y-auto">
          <table class="w-full text-sm">
            <thead class="text-left text-xs text-gray-500 border-b">
              <tr>
                <th class="py-2">#</th>
                <th class="py-2">Nama Subkategori</th>
                <th class="py-2">Kategori Induk</th>
                <th class="py-2">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y">
              @php $count = 0; @endphp
              @foreach($categories as $c)
                @foreach($c->subcategories as $s)
                  @php $count++; @endphp
                  <tr class="hover:bg-gray-50">
                    <td class="py-2">{{ $count }}</td>
                    <td class="py-2 max-w-xs truncate">{{ $s->name }}</td>
                    <td class="py-2 max-w-xs truncate">{{ $c->name }}</td>
                    <td class="py-2">
                      <form method="POST" action="{{ route('it.parameters.subcategory.delete', $s->id) }}" onsubmit="return confirm('Hapus subkategori?');">
                        @csrf
                        <button class="text-red-600 text-sm">Hapus</button>
                      </form>
                    </td>
                  </tr>
                @endforeach
              @endforeach
              @if($count === 0)
                <tr><td colspan="4" class="py-4 text-center text-gray-500">Belum ada subkategori</td></tr>
              @endif
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="bg-white rounded shadow overflow-hidden">
        <div class="flex items-center justify-between p-4 border-b">
          <div class="text-lg font-medium">Root Causes</div>
          <form method="POST" action="{{ route('it.parameters.rootcause.store') }}" class="flex flex-col sm:flex-row items-center gap-2">
            @csrf
            <input name="name" required class="rounded border px-3 py-1 text-sm w-full sm:w-auto sm:flex-1 min-w-0" placeholder="Root cause" />
            <button class="px-3 py-1 bg-sky-600 text-white rounded text-sm">Tambah</button>
          </form>
        </div>

        <div class="p-4 overflow-x-auto max-h-52 overflow-y-auto">
          <table class="w-full text-sm">
            <thead class="text-left text-xs text-gray-500 border-b">
              <tr>
                <th class="py-2">#</th>
                <th class="py-2">Root Cause</th>
                <th class="py-2">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y">
              @foreach($rootCauses as $i => $rc)
                <tr class="hover:bg-gray-50">
                  <td class="py-2">{{ $i + 1 }}</td>
                  <td class="py-2 max-w-xs truncate">{{ $rc->name }}</td>
                  <td class="py-2">
                    <form method="POST" action="{{ route('it.parameters.rootcause.delete', $rc->id) }}" onsubmit="return confirm('Hapus root cause?');">
                      @csrf
                      <button class="text-red-600 text-sm">Hapus</button>
                    </form>
                  </td>
                </tr>
              @endforeach
              @if(count($rootCauses) === 0)
                <tr><td colspan="3" class="py-4 text-center text-gray-500">Belum ada root cause</td></tr>
              @endif
            </tbody>
          </table>
        </div>
      </div>

      <div class="bg-white rounded shadow overflow-hidden">
        <div class="flex items-center justify-between p-4 border-b">
          <div class="text-lg font-medium">Daftar Vendor</div>
          <a href="{{ route('it.users.index') }}" class="px-3 py-1 bg-sky-600 text-white rounded text-sm">Kelola Vendor</a>
        </div>

        <div class="p-4 overflow-x-auto max-h-52 overflow-y-auto">
          <table class="w-full text-sm">
            <thead class="text-left text-xs text-gray-500 border-b">
              <tr>
                <th class="py-2">#</th>
                <th class="py-2">Nama</th>
                <th class="py-2">Email</th>
                <th class="py-2">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y">
              @foreach($vendors as $i => $v)
                <tr class="hover:bg-gray-50">
                  <td class="py-2">{{ $i + 1 }}</td>
                  <td class="py-2 font-medium max-w-xs truncate">{{ $v->name }}</td>
                  <td class="py-2 max-w-xs truncate">{{ $v->email }}</td>
                  <td class="py-2">
                    <a href="{{ route('it.users.edit', $v->id) }}" class="text-sky-600 text-sm">Edit</a>
                  </td>
                </tr>
              @endforeach
              @if(count($vendors) === 0)
                <tr><td colspan="4" class="py-4 text-center text-gray-500">Belum ada vendor</td></tr>
              @endif
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
