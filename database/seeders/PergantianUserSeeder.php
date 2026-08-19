<?php

namespace Database\Seeders;

use App\Models\PergantianUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PergantianUserSeeder extends Seeder
{
    public function run(): void
    {
        $branches = DB::table('kode_kantor')
            ->orderBy('kode')
            ->get(['kode', 'nama_kantor']);

        foreach ($branches as $branch) {
            $base = preg_replace('/[^A-Za-z0-9]+/', '', $branch->nama_kantor);
            $base = $base !== '' ? $base : 'Cabang' . $branch->kode;

            $rows = [
                [
                    'user_name' => 'userlama_' . $branch->kode,
                    'nama_lengkap' => 'User Lama ' . $branch->kode . ' - ' . $branch->nama_kantor,
                ],
                [
                    'user_name' => 'userganti_' . $branch->kode,
                    'nama_lengkap' => 'User Pengganti ' . $branch->kode . ' - ' . $branch->nama_kantor,
                ],
            ];

            foreach ($rows as $row) {
                PergantianUser::updateOrCreate(
                    [
                        'user_name' => $row['user_name'],
                        'unit_kerja' => $branch->kode,
                    ],
                    [
                        'nama_lengkap' => $row['nama_lengkap'],
                    ]
                );
            }
        }
    }
}
