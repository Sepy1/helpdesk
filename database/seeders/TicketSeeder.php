<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;    // <— penting
use Illuminate\Support\Facades\Hash;  // <— kalau dipakai
use App\Models\User;
use App\Models\Ticket;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        // pastikan ada user
        if (User::where('role', 'CABANG')->count() < 5) {
            User::factory()->count(5)->create(['role' => 'CABANG']);
        }
        if (User::where('role', 'IT')->count() < 2) {
            User::factory()->count(2)->create([
                'role'     => 'IT',
                'password' => Hash::make('password'),
            ]);
        }

        // (opsional) kosongkan tickets dulu
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('tickets')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // buat 100 tiket
        Ticket::factory()->count(100)->create();
    }
}
