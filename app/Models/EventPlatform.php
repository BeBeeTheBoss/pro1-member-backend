<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\EventPlatformLink;

class EventPlatform extends Model
{
    protected $table = 'event_platforms';

    protected $fillable = [
        'name',
    ];

    public function eventLinks(): HasMany
    {
        return $this->hasMany(EventPlatformLink::class, 'event_platform_id');
    }
}
