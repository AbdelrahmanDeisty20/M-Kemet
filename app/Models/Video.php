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
        if (filter_var($this->video_path, FILTER_VALIDATE_URL)) {
            return $this->video_path;
        }

        $cleanPath = ltrim(str_replace(['public/', 'storage/'], '', $this->video_path), '/');
        return asset('storage/' . $cleanPath);
    }

    public function getAdminStreamUrlAttribute(): string
    {
        if (\Illuminate\Support\Facades\Route::has('admin.videos.stream')) {
            return route('admin.videos.stream', $this->id);
        }
        return $this->video_url;
    }

    public function getAdminDownloadUrlAttribute(): string
    {
        if (\Illuminate\Support\Facades\Route::has('admin.videos.download')) {
            return route('admin.videos.download', $this->id);
        }
        return $this->video_url;
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->thumbnail_path) {
            return null;
        }

        if (filter_var($this->thumbnail_path, FILTER_VALIDATE_URL)) {
            return $this->thumbnail_path;
        }

        $cleanPath = ltrim(str_replace(['public/', 'storage/'], '', $this->thumbnail_path), '/');
        return asset('storage/' . $cleanPath);
    }
}
