<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_followups_to_tickets_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $t) {
            // kalau kolom 'eskalasi' belum ada
            if (!Schema::hasColumn('tickets','eskalasi')) {
                $t->enum('eskalasi', ['TIDAK','VENDOR'])->default('TIDAK')->after('status');
            }

            // tindak lanjut vendor
            $t->text('vendor_followup')->nullable()->after('eskalasi');
            $t->timestamp('vendor_followup_at')->nullable()->after('vendor_followup');

            // tindak lanjut saat close
            $t->text('closed_note')->nullable()->after('closed_at');
            // kolom taken_at & closed_at diasumsikan sudah ada; jika belum, buka komentar:
            // $t->timestamp('taken_at')->nullable()->after('it_id');
            // $t->timestamp('closed_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $t) {
            $t->dropColumn(['vendor_followup','vendor_followup_at','closed_note']);
            // $t->dropColumn('eskalasi'); // hapus jika mau rollback total
        });
    }
};
