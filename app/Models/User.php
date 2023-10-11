<?php

namespace App\Models;

use App\Models\Group;
use App\Models\Message;
use App\Models\UserSetting;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'phone',
        'email',
        'password',
        'profile_picture',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google2fa_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'google2fa_enabled' => 'boolean',
    ];

    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }

    public function messages()
    {
        // return $this->hasMany(Message::class);
        return $this->hasMany(Message::class, 'user_id');
    }
    public function settings()
    {
        return $this->hasOne(UserSetting::class);
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }


    public function lastMessage()
    {
        return $this->hasMany(Message::class, 'user_id')->orWhere('receiver_id', $this->id)->latest()->first();
    }
}
