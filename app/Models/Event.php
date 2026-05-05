<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $table = 'events';

    protected $fillable = [
        'name',
        'description',
        'image',
        'start_date',
        'end_date',
    ];

    public function platformLinks(): HasMany
    {
        return $this->hasMany(EventPlatformLink::class, 'event_id');
    }
}
