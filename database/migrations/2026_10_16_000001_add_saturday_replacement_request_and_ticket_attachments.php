<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('kind')->default('general');
            $table->timestamps();
        });

        $category = DB::table('categories')->whereRaw('LOWER(name) = ?', ['core banking system'])->first();
        if ($category) {
            $subcategory = DB::table('subcategories')
                ->where('category_id', $category->id)
                ->whereRaw('LOWER(name) = ?', ['manajemen user dan menu'])
                ->first();
            if ($subcategory) {
                DB::table('request_types')->updateOrInsert(
                    ['subcategory_id' => $subcategory->id, 'name' => 'Pergantian Hari Sabtu'],
                    ['is_enabled' => true, 'updated_at' => now(), 'created_at' => now()]
                );
            }
        }
    }

    public function down(): void
    {
        DB::table('request_types')->where('name', 'Pergantian Hari Sabtu')->delete();
        Schema::dropIfExists('ticket_attachments');
    }
};
