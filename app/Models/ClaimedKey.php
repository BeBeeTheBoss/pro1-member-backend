<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClaimedKey extends Model
{
    protected $fillable = ['user_id', 'amount', 'keys', 'claimed_at'];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'keys' => 'integer',
            'claimed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
