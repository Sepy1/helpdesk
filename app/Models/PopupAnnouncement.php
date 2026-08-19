<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PopupAnnouncement extends Model
{
    protected $fillable = [
        'title',
        'description',
        'image_path',
        'is_active',
        'desktop_active',
        'mobile_active',
        'sort_order',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'desktop_active' => 'boolean',
        'mobile_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];
}
