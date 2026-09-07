<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestType extends Model
{
    use HasFactory;

    protected $fillable = ['subcategory_id', 'name', 'is_enabled'];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
