<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PasswordResetRecord extends Model
{
    protected $fillable = [
        'user_id',
        'idcard',
        'phone',
        'reset_type',
        'ip_address',
        'user_agent',
        'device_id',
        'device_name',
        'device_type',
        'app_version',
        'reset_at',
    ];

    protected function casts(): array
    {
        return [
            'reset_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
