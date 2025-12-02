<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Tambah nilai 'VENDOR' ke enum role pada tabel users
        DB::statement("ALTER TABLE users MODIFY role ENUM('IT','CABANG','VENDOR') DEFAULT 'CABANG'");
    }

    public function down(): void
    {
        // Kembalikan ke enum semula (tanpa VENDOR)
        DB::statement("ALTER TABLE users MODIFY role ENUM('IT','CABANG') DEFAULT 'CABANG'");
    }
};
