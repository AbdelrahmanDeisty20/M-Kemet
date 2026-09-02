<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    protected $fillable = ['user_id', 'document_type', 'file_path', 'disk', 'is_approved', 'rejection_reason'];

    protected $casts = [
        'is_approved' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getSecureUrlAttribute(): string
    {
        if ($this->document_type === 'personal_photo' && $this->disk === 'public') {
            return Storage::disk('public')->url($this->file_path);
        }

        return url("/api/documents/{$this->id}/file");
    }
}
