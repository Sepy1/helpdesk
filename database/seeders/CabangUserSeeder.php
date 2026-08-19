<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CabangUserSeeder extends Seeder
{
    public function run(): void
    {
        $branches = DB::table('kode_kantor')
            ->orderBy('kode')
            ->get(['kode', 'nama_kantor']);

        foreach ($branches as $branch) {
            $username = 'cabang_' . $branch->kode;
            $name = 'User Cabang ' . $branch->kode . ' - ' . $branch->nama_kantor;
            $email = $username . '@helpdesk.local';

            User::updateOrCreate(
                ['username' => $username],
                [
                    'name' => $name,
                    'email' => $email,
                    'role' => 'CABANG',
                    'kode_kantor' => $branch->kode,
                    'password' => Hash::make('password'),
                ]
            );
        }
    }
}
