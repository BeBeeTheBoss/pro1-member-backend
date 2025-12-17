<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SelectedCoupon extends Model
{
    protected $fillable = ['user_id', 'coupon_id', 'expiry_date','coupon_type', 'is_used','used_at','coupon_name'];
}
