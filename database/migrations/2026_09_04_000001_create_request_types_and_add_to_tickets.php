<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subcategory_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
            $table->unique(['subcategory_id', 'name']);
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('request_type_id')->nullable()->after('subcategory_id')
                ->constrained('request_types')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('request_type_id');
        });
        Schema::dropIfExists('request_types');
    }
};
