<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChangelogEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'version', 'title', 'type', 'summary', 'details', 'release_date',
        'is_published', 'created_by', 'published_at',
    ];

    protected $casts = [
        'release_date' => 'date',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
