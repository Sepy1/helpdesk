<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TicketDummySeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            // Pastikan ada user IT & Cabang
            if (User::where('role','IT')->count() === 0) {
                foreach (['erik','yudha','admin'] as $u) {
                    User::create([
                        'name' => ucfirst($u).' (IT)',
                        'username' => $u,
                        'email' => $u.'@example.test',
                        'role' => 'IT',
                        'password' => Hash::make('password'),
                    ]);
                }
            }

            if (User::where('role','CABANG')->count() < 5) {
                for ($i=1; $i<=10; $i++) {
                    $code = str_pad($i,3,'0',STR_PAD_LEFT);
                    User::firstOrCreate(
                        ['username'=>$code],
                        [
                            'name' => 'Cabang '.$code,
                            'email'=> $code.'@example.test',
                            'role' => 'CABANG',
                            'password'=> Hash::make('password'),
                        ]
                    );
                }
            }

            // (opsional) kosongkan tiket dulu
            // DB::statement('SET FOREIGN_KEY_CHECKS=0');
            // \App\Models\TicketComment::truncate();
            // Ticket::truncate();
            // DB::statement('SET FOREIGN_KEY_CHECKS=1');

            // Buat 100 tiket
            Ticket::factory()->count(100)->create();
        });
    }
}
