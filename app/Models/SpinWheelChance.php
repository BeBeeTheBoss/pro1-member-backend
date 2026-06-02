<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpinWheelChance extends Model
{
    protected $fillable = [
        'points',
        'max_times',
        'type',
    ];
}
