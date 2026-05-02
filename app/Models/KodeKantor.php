<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KodeKantor extends Model
{
    protected $table = 'kode_kantor';

    protected $primaryKey = 'kode';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'kode',
        'nama_kantor',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'kode_kantor', 'kode');
    }
}
