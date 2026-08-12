<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'title',
        'message',
        'recipient',
        'image',
        'recipient_file',
        'recipient_file_original_name',
        'recipient_file_mime_type',
        'recipient_file_size',
        'route_to',
        'is_manual'
    ];

    public function userNotifications()
    {
        return $this->hasMany(UserNotification::class);
    }


}
