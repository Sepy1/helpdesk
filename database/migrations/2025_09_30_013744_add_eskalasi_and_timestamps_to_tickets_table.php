<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $t) {
            $t->enum('eskalasi', ['VENDOR','TIDAK'])->nullable()->after('it_id');
            $t->timestamp('taken_at')->nullable()->after('status');
            $t->timestamp('closed_at')->nullable()->after('taken_at');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $t) {
            $t->dropColumn(['eskalasi', 'taken_at', 'closed_at']);
        });
    }
};
