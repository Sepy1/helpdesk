<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'role',
        'kode_kantor',
        'visible_on_assign',
        'ai_chat_enabled',
        'email_notifications_enabled',
        'android_notifications_enabled',
        'no_hp',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'visible_on_assign' => 'boolean',
        'ai_chat_enabled' => 'boolean',
        'email_notifications_enabled' => 'boolean',
        'android_notifications_enabled' => 'boolean',
    ];

    public function devices()
    {
        return $this->hasMany(UserDevice::class);
    }

    public function kodeKantor()
    {
        return $this->belongsTo(KodeKantor::class, 'kode_kantor', 'kode');
    }

    public function aiChatMessages()
    {
        return $this->hasMany(AiChatMessage::class);
    }
}
