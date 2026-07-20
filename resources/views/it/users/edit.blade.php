@extends('layouts.app')
@section('title','Edit User')

@section('content')
@php
  $roles = ['IT', 'CABANG', 'VENDOR', 'ADMIN'];
  $input = 'mt-1 h-10 w-full rounded-lg border-slate-200 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500';
  $label = 'text-xs font-semibold uppercase tracking-wide text-slate-500';
  $btnPrimary = 'inline-flex h-10 items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500';
  $btnSecondary = 'inline-flex h-10 items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500';
@endphp

<div class="w-full max-w-5xl pb-8">
  <div class="space-y-4">
    <section class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <div class="text-xs font-medium text-slate-500">User Access</div>
        <h1 class="mt-2 text-2xl font-semibold tracking-normal text-slate-900 sm:text-3xl">Edit User</h1>
        <p class="mt-1 text-sm text-slate-500">{{ $user->name }} - {{ $user->email }}</p>
      </div>
      <a href="{{ route('it.users.index') }}" class="{{ $btnSecondary }}">Kembali</a>
    </section>

    @if($errors->any())
      <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-sm">{{ $errors->first() }}</div>
    @endif

    <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
      <form method="POST" action="{{ route('it.users.update', $user) }}" class="p-4 sm:p-5">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div>
            <label for="username" class="{{ $label }}">Username</label>
            <input id="username" type="text" name="username" value="{{ old('username', $user->username) }}" class="{{ $input }}" required>
            @error('username')<div class="mt-1 text-xs text-red-600">{{ $message }}</div>@enderror
          </div>
          <div>
            <label for="name" class="{{ $label }}">Nama</label>
            <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" class="{{ $input }}" required>
            @error('name')<div class="mt-1 text-xs text-red-600">{{ $message }}</div>@enderror
          </div>
          <div>
            <label for="email" class="{{ $label }}">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" class="{{ $input }}" required>
            @error('email')<div class="mt-1 text-xs text-red-600">{{ $message }}</div>@enderror
          </div>
          <div>
            <label for="role" class="{{ $label }}">Role</label>
            <select id="role" name="role" class="{{ $input }}" required>
              @foreach($roles as $role)
                <option value="{{ $role }}" @selected(old('role', $user->role) === $role)>{{ $role }}</option>
              @endforeach
            </select>
            @error('role')<div class="mt-1 text-xs text-red-600">{{ $message }}</div>@enderror
          </div>
          <div>
            <label for="kode_kantor" class="{{ $label }}">Kode kantor</label>
            <select id="kode_kantor" name="kode_kantor" class="{{ $input }}">
              <option value="">Tidak dipilih</option>
              @foreach($kodeKantors as $office)
                <option value="{{ $office->kode }}" @selected(old('kode_kantor', $user->kode_kantor) === $office->kode)>{{ $office->kode }} - {{ $office->nama_kantor }}</option>
              @endforeach
            </select>
            @error('kode_kantor')<div class="mt-1 text-xs text-red-600">{{ $message }}</div>@enderror
          </div>
          <div>
            <label for="password" class="{{ $label }}">Password baru</label>
            <input id="password" type="password" name="password" class="{{ $input }}" placeholder="Kosongkan jika tidak diganti">
            @error('password')<div class="mt-1 text-xs text-red-600">{{ $message }}</div>@enderror
          </div>
          <div class="md:col-span-2 grid grid-cols-1 gap-3 sm:grid-cols-2">
            <label class="flex items-center gap-3 rounded-lg border border-slate-200 px-3 py-2">
              <input type="hidden" name="email_notifications_enabled" value="0">
              <input type="checkbox" name="email_notifications_enabled" value="1" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" @checked(old('email_notifications_enabled', $user->email_notifications_enabled))>
              <span>
                <span class="block text-sm font-semibold text-slate-800">Notifikasi Email</span>
                <span class="block text-xs text-slate-500">Aktifkan atau matikan email notifikasi</span>
              </span>
            </label>
            <label class="flex items-center gap-3 rounded-lg border border-slate-200 px-3 py-2">
              <input type="hidden" name="android_notifications_enabled" value="0">
              <input type="checkbox" name="android_notifications_enabled" value="1" class="h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500" @checked(old('android_notifications_enabled', $user->android_notifications_enabled))>
              <span>
                <span class="block text-sm font-semibold text-slate-800">Notifikasi Android</span>
                <span class="block text-xs text-slate-500">Aktifkan atau matikan push Android</span>
              </span>
            </label>
          </div>
        </div>

        <div class="mt-5 flex flex-wrap justify-end gap-2 border-t border-slate-100 pt-4">
          <a href="{{ route('it.users.index') }}" class="{{ $btnSecondary }}">Batal</a>
          <button type="submit" class="{{ $btnPrimary }}">Simpan Perubahan</button>
        </div>
      </form>
    </section>
  </div>
</div>
@endsection
