<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('root_cause_followups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('root_cause_id')->constrained('root_causes')->cascadeOnDelete();
            $table->string('label', 191);
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_other')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('root_cause_followups');
    }
};
