<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    // generator nomor unik: TCK-YYYYMM-XXXX (atomic)
    private function genNo(): string
    {
        $prefix = 'TCK-' . now()->format('Ym') . '-';
        return DB::transaction(function () use ($prefix) {
            $last = DB::table('tickets')
                ->where('nomor_tiket', 'like', $prefix.'%')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->value('nomor_tiket');
            $seq = $last ? ((int) substr($last, -4) + 1) : 1;
            return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
        });
    }

    public function definition(): array
    {
        $kategori = fake()->randomElement(['JARINGAN','LAYANAN','CBS','OTHER']);
        $status   = fake()->randomElement(['OPEN','ON_PROGRESS','CLOSED']);

        // user cabang acak
        $userId = User::where('role','CABANG')->inRandomOrder()->value('id')
               ?? User::factory()->create(['role'=>'CABANG'])->id;

        // handler IT jika status ON_PROGRESS/CLOSED
        $itId = null;
        if ($status !== 'OPEN') {
            $itId = User::where('role','IT')->inRandomOrder()->value('id')
                 ?? User::factory()->create(['role'=>'IT'])->id;
        }

        return [
            'nomor_tiket' => $this->genNo(),
            'user_id'     => $userId,
            'it_id'       => $itId,
            'kategori'    => $kategori,
            'deskripsi'   => fake()->sentence(12),
            'lampiran'    => null,  // biarkan null untuk dummy
            'status'      => $status,
            'created_at'  => fake()->dateTimeBetween('-90 days','now'),
            'updated_at'  => now(),
        ];
    }
}
