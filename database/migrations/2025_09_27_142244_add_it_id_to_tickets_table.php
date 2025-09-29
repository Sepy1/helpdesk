<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // kalau belum ada kolom it_id, tambahkan
            if (!Schema::hasColumn('tickets', 'it_id')) {
                // Opsi A: dengan foreign key (butuh tabel users)
                $table->foreignId('it_id')->nullable()
                      ->constrained('users')->nullOnDelete();

                // Jika DB-mu bermasalah dengan constrained(), pakai opsi B:
                // $table->unsignedBigInteger('it_id')->nullable()->after('status');
                // $table->foreign('it_id')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (Schema::hasColumn('tickets', 'it_id')) {
                $table->dropForeign(['it_id']);
                $table->dropColumn('it_id');
            }
        });
    }
};
