<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpinRecord extends Model
{
    protected $table = 'spin_records';

    protected $fillable = [
        'user_id',
        'spin_wheel_chance_daily_id',
        'at_max_times',
        'reward_points',
        'spun_at',
        'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function spinWheelChanceDaily(): BelongsTo
    {
        return $this->belongsTo(SpinWheelChanceDaily::class, 'spin_wheel_chance_daily_id');
    }
}
