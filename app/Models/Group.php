<?php

namespace App\Models;

use App\Models\User;
use App\Models\Message;
use App\Models\GroupUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'group_user');
    }


    public function conversations()
    {
        return $this->hasMany(Message::class);
    }
    public function mediafiles()
    {
        return $this->hasMany(Message::class)->whereIn('type', array('image', 'video', 'audio'));
    }
}
