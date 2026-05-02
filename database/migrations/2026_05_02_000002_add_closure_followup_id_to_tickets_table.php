<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('closure_followup_id')
                ->nullable()
                ->after('root_cause')
                ->constrained('root_cause_followups')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['closure_followup_id']);
            $table->dropColumn('closure_followup_id');
        });
    }
};
