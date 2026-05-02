<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RootCauseDetail extends Model
{
    protected $table = 'root_cause_details';

    protected $fillable = [
        'root_cause_id',
        'label',
        'sort',
        'is_other',
    ];

    protected $casts = [
        'is_other' => 'boolean',
        'sort' => 'integer',
    ];

    public function rootCause(): BelongsTo
    {
        return $this->belongsTo(RootCause::class);
    }
}
