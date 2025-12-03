@extends('layouts.app')
@section('title','Tambah User')

@section('content')
<div class="max-w-lg mx-auto bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
  <h2 class="text-lg font-semibold text-gray-800 mb-4">Tambah User</h2>

  <form method="POST" action="{{ route('it.users.store') }}" class="space-y-4">
    @csrf
    <div>
      <label class="text-sm font-medium text-gray-700">Username</label>
      <input type="text" name="username" value="{{ old('username') }}" class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required />
      @error('username')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
    </div>
    <div>
      <label class="text-sm font-medium text-gray-700">Nama</label>
      <input type="text" name="name" value="{{ old('name') }}" class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required />
      @error('name')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
    </div>
    <div>
      <label class="text-sm font-medium text-gray-700">Email</label>
      <input type="email" name="email" value="{{ old('email') }}" class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required />
      @error('email')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
    </div>
    <div>
      <label class="text-sm font-medium text-gray-700">Password</label>
      <input type="password" name="password" class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required />
      @error('password')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
    </div>
    <div>
      <label class="text-sm font-medium text-gray-700">Role</label>
      <select name="role" class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
        <option value="IT">IT</option>
        <option value="CABANG">CABANG</option>
        <option value="VENDOR">VENDOR</option>
        <option value="ADMIN">ADMIN</option>
      </select>
      @error('role')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
    </div>
    <div class="flex justify-end gap-2">
      <a href="{{ route('it.users.index') }}" class="rounded-lg px-3 py-2 text-sm text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50">Batal</a>
      <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700">Simpan</button>
    </div>
  </form>
</div>
@endsection
