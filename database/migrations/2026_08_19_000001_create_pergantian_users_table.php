<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pergantian_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name');
            $table->string('nama_lengkap');
            $table->string('unit_kerja', 3)->index();
            $table->timestamps();

            $table->index(['unit_kerja', 'nama_lengkap']);
            $table->index(['unit_kerja', 'user_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pergantian_users');
    }
};
