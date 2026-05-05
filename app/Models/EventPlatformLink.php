<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventPlatformLink extends Model
{
    protected $table = 'event_platform_links';

    protected $fillable = [
        'event_id',
        'event_platform_id',
        'link',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function platform(): BelongsTo
    {
        return $this->belongsTo(EventPlatform::class, 'event_platform_id');
    }
}
