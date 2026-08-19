<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PergantianUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_name',
        'nama_lengkap',
        'unit_kerja',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
