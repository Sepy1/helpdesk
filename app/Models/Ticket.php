<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;
     protected $fillable = [
        'nomor_tiket','user_id','kategori','deskripsi','lampiran','status',
        'it_id','taken_at','progress_note','progress_at','eskalasi',
        'vendor_followup','vendor_followup_at','closed_note','closed_at', 'root_cause',
    ];

    protected $casts = [
    'created_at'         => 'datetime',
        'updated_at'         => 'datetime',
        'taken_at'           => 'datetime',
        'progress_at'        => 'datetime',
        'vendor_followup_at' => 'datetime',
        'closed_at'          => 'datetime',
        'root_cause',
        'closed_note',
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
