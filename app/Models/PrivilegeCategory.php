<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Privilege;

class PrivilegeCategory extends Model
{
    protected $table = 'privilege_categories';

    protected $fillable = [
        'name',
    ];

    public function privileges(): HasMany
    {
        return $this->hasMany(Privilege::class, 'category_id');
    }
}
