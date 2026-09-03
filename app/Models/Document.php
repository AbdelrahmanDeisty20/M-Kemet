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

    public function getUrlAttribute(): string
    {
        return $this->secure_url;
    }

    public function getSecureUrlAttribute(): string
    {
        if (filter_var($this->file_path, FILTER_VALIDATE_URL)) {
            return $this->file_path;
        }

        if ($this->disk === 'public' || $this->document_type === 'personal_photo') {
            return Storage::disk('public')->url($this->file_path);
        }

        if (\Illuminate\Support\Facades\Route::has('documents.file')) {
            return route('documents.file', $this->id);
        }

        if (\Illuminate\Support\Facades\Route::has('admin.documents.file')) {
            return route('admin.documents.file', $this->id);
        }

        return Storage::url($this->file_path);
    }

    public function getAdminFileUrlAttribute(): string
    {
        return route('admin.documents.file', $this->id);
    }

    public function getAdminDownloadUrlAttribute(): string
    {
        return route('admin.documents.download', $this->id);
    }

    public function getIsImageAttribute(): bool
    {
        $ext = strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']) || $this->document_type === 'personal_photo';
    }

    public function getIsPdfAttribute(): bool
    {
        $ext = strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION));
        return $ext === 'pdf' || $this->document_type === 'cv';
    }

    public function getDocumentTypeNameAttribute(): string
    {
        return match ($this->document_type) {
            'cv'             => 'السيرة الذاتية (CV)',
            'national_id'    => 'الهوية الوطنية',
            'passport'       => 'جواز السفر',
            'personal_photo' => 'صورة شخصية',
            default          => $this->document_type,
        };
    }
}
