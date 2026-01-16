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
        'route_to',
        'is_manual'
    ];

    public function userNotifications()
    {
        return $this->hasMany(UserNotification::class);
    }


}
