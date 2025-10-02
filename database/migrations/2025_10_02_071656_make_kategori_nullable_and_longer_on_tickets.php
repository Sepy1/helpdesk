<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeKategoriNullableAndLongerOnTickets extends Migration
{
    public function up()
    {
        Schema::table('tickets', function (Blueprint $table) {
            // ubah panjang menjadi 255 dan boleh null
            $table->string('kategori', 255)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('tickets', function (Blueprint $table) {
            // rollback: kembalikan ke panjang kecil (ganti 50 sesuai kondisi sebelumnya)
            $table->string('kategori', 50)->nullable()->change();
        });
    }
}
