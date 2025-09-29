<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ResetUsersSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Matikan FK (jika ada relasi ke users), kosongkan tabel users
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            User::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            // Helper bikin user
            $make = function (string $username, string $role, string $name = null) {
                return User::create([
                    'name'     => $name ?: strtoupper($username),
                    'username' => $username,
                    // email dummy unik jika kolom email masih unique
                    'email'    => $username.'@example.test',
                    'role'     => $role,       // 'IT' atau 'CABANG'
                    'password' => Hash::make('password'),
                ]);
            };

            // === IT ===
            $make('erik',  'IT', 'Erik Pratama Yudha');
            $make('yuda', 'IT', 'Yuda Hardiadi Putra');
            $make('admin', 'IT', 'Admin IT');

            // === CABANG 001..028 ===
            for ($i = 1; $i <= 28; $i++) {
                $u = str_pad((string)$i, 3, '0', STR_PAD_LEFT); // 001, 002, ..., 028
                $make($u, 'CABANG', 'Cabang '.$u);
            }
        });
    }
}
