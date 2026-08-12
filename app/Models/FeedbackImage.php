<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedbackImage extends Model
{
    protected $fillable = [
        'feedback_id',
        'image',
    ];

    protected $appends = [
        'image_url',
    ];

    public function feedback(): BelongsTo
    {
        return $this->belongsTo(Feedback::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        return url('storage/feedbacks/' . $this->image);
    }
}
