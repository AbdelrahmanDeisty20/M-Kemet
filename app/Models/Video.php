<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Video extends Model
{
    protected $fillable = [
        'user_id',
        'video_path',
        'thumbnail_path',
        'duration_seconds',
        'file_size_mb',
        'status',
        'rejection_reason',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getVideoUrlAttribute(): string
    {
        return route('admin.videos.stream', $this->id);
    }

    public function getAdminStreamUrlAttribute(): string
    {
        return route('admin.videos.stream', $this->id);
    }

    public function getAdminDownloadUrlAttribute(): string
    {
        return route('admin.videos.download', $this->id);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail_path ? Storage::disk('public')->url($this->thumbnail_path) : null;
    }
}
