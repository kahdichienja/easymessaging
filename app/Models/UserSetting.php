<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'primary_color',
        'timezone',
        'language',
        'theme',

        'online_status',
        'notification_enabled',
        'sms_notifications_enabled',
        'email_notifications_enabled',
        // Add more fillable columns for additional settings if needed
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'online_status' => 'boolean',
        'notification_enabled' => 'boolean',
        'sms_notifications_enabled' => 'boolean',
        'email_notifications_enabled' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
