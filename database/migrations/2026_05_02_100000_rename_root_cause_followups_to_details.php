<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['closure_followup_id']);
        });

        Schema::rename('root_cause_followups', 'root_cause_details');

        Schema::table('tickets', function (Blueprint $table) {
            $table->renameColumn('closure_followup_id', 'root_cause_detail_id');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->foreign('root_cause_detail_id')
                ->references('id')
                ->on('root_cause_details')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['root_cause_detail_id']);
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->renameColumn('root_cause_detail_id', 'closure_followup_id');
        });

        Schema::rename('root_cause_details', 'root_cause_followups');

        Schema::table('tickets', function (Blueprint $table) {
            $table->foreign('closure_followup_id')
                ->references('id')
                ->on('root_cause_followups')
                ->nullOnDelete();
        });
    }
};
