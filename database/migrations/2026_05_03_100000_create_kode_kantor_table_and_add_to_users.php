<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kode_kantor', function (Blueprint $table) {
            $table->string('kode', 3)->primary();
            $table->string('nama_kantor', 191);
            $table->timestamps();
        });

        $rows = [
            ['001', 'Cabang Utama'],
            ['002', 'Rembang'],
            ['003', 'Pati'],
            ['004', 'Demak'],
            ['005', 'Kendal'],
            ['006', 'Salatiga'],
            ['007', 'Semarang'],
            ['008', 'Wonogiri'],
            ['009', 'Kota Surakarta'],
            ['010', 'Karanganyar'],
            ['011', 'Sukoharjo'],
            ['012', 'Sragen'],
            ['013', 'Boyolali'],
            ['014', 'Magelang'],
            ['015', 'Wonosobo'],
            ['016', 'Purworejo'],
            ['017', 'Kebumen'],
            ['018', 'Banjarnegara'],
            ['019', 'Purbalingga'],
            ['020', 'Banyumas'],
            ['021', 'Cilacap'],
            ['022', 'Tegal'],
            ['023', 'Brebes'],
            ['024', 'Kota Tegal'],
            ['025', 'Pemalang'],
            ['026', 'Kota Pekalongan'],
            ['027', 'Kab. Pekalongan'],
            ['028', 'Batang'],
        ];

        $now = now();
        foreach ($rows as [$kode, $nama]) {
            DB::table('kode_kantor')->insert([
                'kode' => $kode,
                'nama_kantor' => $nama,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('kode_kantor', 3)->nullable()->after('role');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('kode_kantor')
                ->references('kode')
                ->on('kode_kantor')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['kode_kantor']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('kode_kantor');
        });
        Schema::dropIfExists('kode_kantor');
    }
};
