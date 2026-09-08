<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'CBS' => 'Core Banking System',
            'JARINGAN' => 'Network',
            'LAYANAN' => 'Layanan Digital',
            'OTHER' => 'Lainnya',
        ] as $legacyName => $categoryName) {
            $categoryId = DB::table('categories')->where('name', $categoryName)->value('id');
            if ($categoryId) {
                DB::table('tickets')->whereNull('category_id')->where('kategori', $legacyName)
                    ->update(['category_id' => $categoryId]);
            }
        }
    }

    public function down(): void
    {
        // Pertahankan relasi kategori yang sudah diperbaiki.
    }
};
