<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpinWheelChanceDaily extends Model
{
    protected $table = 'spin_wheel_chances_daily';

    protected $fillable = [
        'date',
        'points',
        'max_times',
        'type',
    ];
}
