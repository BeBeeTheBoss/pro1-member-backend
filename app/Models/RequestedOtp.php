<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestedOtp extends Model
{
    protected $fillable = ['phone','otp','expire_at'];
}
