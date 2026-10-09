<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comment_shortcuts', function (Blueprint $table) {
            $table->id();
            $table->string('text', 500)->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();
        DB::table('comment_shortcuts')->insert([
            ['text' => 'Silahkan dicoba kembali', 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['text' => 'Tolong lampirkan foto / surat', 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['text' => 'Mohon ditunggu sedang kami cek', 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('comment_shortcuts');
    }
};
