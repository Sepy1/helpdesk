<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommentShortcut extends Model
{
    protected $fillable = ['text', 'sort_order'];
}
