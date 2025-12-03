@extends('layouts.app')
@section('title','Profil')

@section('content')
<div class="max-w-xl mx-auto bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
  <h2 class="text-lg font-semibold text-gray-800 mb-2">Profil</h2>
  <p class="text-sm text-gray-600 mb-6">Ubah password akun Anda.</p>

  @if(session('success'))
    <div class="mb-4 rounded-lg bg-green-50 text-green-700 px-4 py-3 border border-green-200 shadow-md">{{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="mb-4 rounded-lg bg-red-50 text-red-700 px-4 py-3 border border-red-200 shadow-md">
      <ul class="list-disc pl-5 text-sm">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('profile.password.update') }}" class="space-y-4">
    @csrf
    <div>
      <label class="text-sm font-medium text-gray-700">Password Saat Ini</label>
      <input type="password" name="current_password" class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required />
    </div>
    <div>
      <label class="text-sm font-medium text-gray-700">Password Baru</label>
      <input type="password" name="password" class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required />
    </div>
    <div>
      <label class="text-sm font-medium text-gray-700">Konfirmasi Password Baru</label>
      <input type="password" name="password_confirmation" class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required />
    </div>
    <div class="flex justify-end">
      <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">Simpan</button>
    </div>
  </form>
</div>
@endsection
