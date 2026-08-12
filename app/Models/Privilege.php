<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\PrivilegeCategory;

class Privilege extends Model
{
    protected $table = 'privileges';

    protected $fillable = [
        'title',
        'description',
        'image',
        'start_date',
        'end_date',
        'category_id',
        'is_active',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(PrivilegeCategory::class, 'category_id');
    }
}
