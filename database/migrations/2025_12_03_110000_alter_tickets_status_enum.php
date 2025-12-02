<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Adjust this to your DB: using MySQL ENUM alteration
        DB::statement("ALTER TABLE tickets MODIFY COLUMN status ENUM('OPEN','ON_PROGRESS','ESKALASI_VENDOR','VENDOR_RESOLVED','CLOSED') NOT NULL DEFAULT 'OPEN'");
    }

    public function down(): void
    {
        // Revert to original set if needed
        DB::statement("ALTER TABLE tickets MODIFY COLUMN status ENUM('OPEN','ON_PROGRESS','CLOSED') NOT NULL DEFAULT 'OPEN'");
    }
};
