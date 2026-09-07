<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subcategory extends Model
{
    use HasFactory;

    protected $fillable = ['category_id','name','slug','description','is_enabled'];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function requestTypes()
    {
        return $this->hasMany(RequestType::class);
    }
}
