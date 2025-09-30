<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;
     protected $fillable = [
        'nomor_tiket','user_id','kategori','deskripsi','lampiran','status','it_id','eskalasi','taken_at','closed_at',
    ];

    protected $casts = [
    'taken_at'  => 'datetime',
    'closed_at' => 'datetime',
];

    public function user() {        // pembuat (cabang)
        return $this->belongsTo(User::class, 'user_id');
    }

    public function it() {          // handler (IT)
        return $this->belongsTo(User::class, 'it_id');
    }

    public function comments(){ return $this->hasMany(TicketComment::class)->latest(); 
    }
}
