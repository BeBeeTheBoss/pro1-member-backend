<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'name',
        'address',
        'contact',
        'opening_time',
        'closing_time',
        'latitude',
        'longitude',
        'is_active',
        'region',
        'township',
        'image'
    ];
}
