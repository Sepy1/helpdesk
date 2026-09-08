<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('changelog_entries', function (Blueprint $table) {
            $table->id();
            $table->string('version', 50)->nullable();
            $table->string('title');
            $table->string('type', 30)->default('changed');
            $table->text('summary')->nullable();
            $table->longText('details');
            $table->date('release_date');
            $table->boolean('is_published')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['is_published', 'release_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('changelog_entries');
    }
};
