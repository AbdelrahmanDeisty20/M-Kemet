<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title_ar',
        'title_en',
        'message_ar',
        'message_en',
        'type',
        'data',
        'is_read',
    ];

    protected $casts = [
        'data'    => 'array',
        'is_read' => 'boolean',
    ];

    protected $attributes = [
        'data' => '[]',
    ];

    public function getTitleAttribute(): ?string
    {
        return app()->getLocale() === 'en'
            ? ($this->title_en ?? $this->title_ar)
            : ($this->title_ar ?? $this->title_en);
    }

    public function getMessageAttribute(): ?string
    {
        return app()->getLocale() === 'en'
            ? ($this->message_en ?? $this->message_ar)
            : ($this->message_ar ?? $this->message_en);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
