@extends('layouts.app')
@section('title','Manajemen User')

@section('content')
<div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-lg font-semibold text-gray-800">Manajemen User</h2>
    <a href="{{ route('it.users.create') }}" class="rounded-lg bg-emerald-600 px-3 py-2 text-white hover:bg-emerald-700">Tambah User</a>
  </div>

  <form method="GET" action="{{ route('it.users.index') }}" class="mb-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
      <div>
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / email / username" class="w-full rounded-lg h-10 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
      </div>
      <div>
        <select name="role" class="w-full rounded-lg h-10 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
          <option value="">Semua Role</option>
          @foreach(['IT','CABANG','VENDOR','ADMIN'] as $r)
            <option value="{{ $r }}" @selected(request('role')===$r)>{{ $r }}</option>
          @endforeach
        </select>
      </div>
      <div class="flex md:justify-end">
        <button class="w-full md:w-auto rounded-lg h-10 bg-indigo-600 px-4 text-white hover:bg-indigo-700">Filter</button>
      </div>
    </div>
  </form>

  @if(session('success'))
    <div class="mb-4 rounded-lg bg-green-50 text-green-700 px-4 py-3 border border-green-200 shadow-md">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="mb-4 rounded-lg bg-red-50 text-red-700 px-4 py-3 border border-red-200 shadow-md">{{ session('error') }}</div>
  @endif

  <div class="overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-gray-50 text-gray-600">
        <tr>
          <th class="py-2 px-3 text-left">Nama</th>
          <th class="py-2 px-3 text-left">Email</th>
          <th class="py-2 px-3 text-left">Username</th>
          <th class="py-2 px-3 text-left">Role</th>
          <th class="py-2 px-3 text-left">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        @foreach($users as $u)
        <tr>
          <td class="py-2 px-3">{{ $u->name }}</td>
          <td class="py-2 px-3">{{ $u->email }}</td>
          <td class="py-2 px-3">{{ $u->username }}</td>
          <td class="py-2 px-3">{{ $u->role }}</td>
          <td class="py-2 px-3">
            <div class="flex flex-col sm:flex-row gap-2">
              <a href="{{ route('it.users.edit',$u) }}" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-white hover:bg-indigo-700 text-center">Edit</a>
              <form method="POST" action="{{ route('it.users.destroy',$u) }}" onsubmit="return confirm('Hapus user ini?')">
                @csrf @method('DELETE')
                <button class="w-full sm:w-auto rounded-lg bg-red-600 px-3 py-1.5 text-white hover:bg-red-700">Delete</button>
              </form>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="mt-4">{!! $users->links('pagination::tailwind') !!}</div>
</div>
@endsection
