<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSession extends Model
{
    protected $fillable = ['user_id','session_start','session_end','duration_in_seconds'];
}
