<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
             $table->id();
    $table->string('nomor_tiket')->unique();
    $table->foreignId('user_id')->constrained('users');
    $table->enum('kategori', ['JARINGAN','LAYANAN','CBS','OTHER']);
    $table->text('deskripsi');
    $table->string('lampiran')->nullable();
    $table->enum('status', ['OPEN','ON_PROGRESS','CLOSED'])->default('OPEN');
    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tickets');
    }
};
