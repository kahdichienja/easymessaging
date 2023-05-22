<?php

namespace App\Models;

use App\Models\User;
use App\Models\Group;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'receiver_id',
        'group_id',
        'content',
        'file',
        'is_read',
    ];

    protected $dates = [
        'deleted_at',
    ];

    protected $casts = ['is_read' => 'boolean'];

    public static $enums = [
        'type' => [
            'image' => 'Image',
            'video' => 'Video',
            'audio' => 'Audio',
        ],
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function sender()
    {
        return $this->belongsTo(User::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
