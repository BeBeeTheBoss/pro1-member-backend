<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsedCouponLog extends Model
{
    protected $fillable = [
        'user_id',
        'points',
        'coupon_id',
        'coupon_type'
    ];
}
