<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $entries = [
            [
                'version' => '2026.09.08',
                'title' => 'Penyempurnaan All Tiket dan Detail Tiket',
                'type' => 'changed',
                'summary' => 'Ringkasan tiket, SLA kategori, infinite scroll, dan pengalaman komentar diperbarui.',
                'details' => "• Card ringkasan dan filter cepat pada All Tiket.\n• Infinite scroll pada daftar tiket.\n• SLA per kategori dengan satuan Jam atau Hari.\n• Tampilan Informasi Tiket dua kolom.\n• Preview gambar komentar mendukung zoom, geser, dan rotasi.\n• Dropdown kategori mengikuti tema aplikasi.",
                'release_date' => '2026-09-08',
            ],
            [
                'version' => '2026.09.07',
                'title' => 'Klasifikasi, Parameter, dan Rekap Statistik',
                'type' => 'added',
                'summary' => 'Penambahan jenis permintaan, tree parameter, tema aplikasi, dan rekap statistik.',
                'details' => "• Jenis Permintaan pada alur pembuatan dan detail tiket.\n• Tree kategori, subkategori, jenis permintaan, root cause, dan detail root cause.\n• Pilihan tema global aplikasi.\n• Penyempurnaan statistik dan rekap PDF.",
                'release_date' => '2026-09-07',
            ],
        ];

        foreach ($entries as $entry) {
            if (! DB::table('changelog_entries')->where('version', $entry['version'])->exists()) {
                DB::table('changelog_entries')->insert(array_merge($entry, [
                    'is_published' => true,
                    'published_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            }
        }
    }

    public function down(): void
    {
        DB::table('changelog_entries')->whereIn('version', ['2026.09.07', '2026.09.08'])->delete();
    }
};
