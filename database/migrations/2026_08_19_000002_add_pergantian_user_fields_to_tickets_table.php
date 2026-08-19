<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('user_lama_id')->nullable()->after('subcategory_id')->constrained('pergantian_users')->nullOnDelete();
            $table->foreignId('user_pengganti_id')->nullable()->after('user_lama_id')->constrained('pergantian_users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_pengganti_id');
            $table->dropConstrainedForeignId('user_lama_id');
        });
    }
};
