<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GamesEvent extends Model
{
    protected $table = 'games_event';

    protected $fillable = [
        'name',
        'description',
        'image',
        'type',
        'minimum_purchase_amount',
        'start_date',
        'start_time',
        'end_date',
        'end_time',
        'is_active',
        'all_branches',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'all_branches' => 'boolean',
        'minimum_purchase_amount' => 'decimal:2',
    ];

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'games_event_branch', 'games_event_id', 'branch_id')
            ->withTimestamps();
    }
}
