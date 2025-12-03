@extends('layouts.app')
@section('title','Profil Saya')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Akun</h2>
        <div class="max-w-xl">
            @include('profile.partials.update-profile-information-form')
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Ubah Password</h2>
        <div class="max-w-xl">
            @include('profile.partials.update-password-form')
        </div>
    </div>

  
</div>
@endsection
