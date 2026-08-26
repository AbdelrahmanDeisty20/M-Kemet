<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CompanyDocument extends Model
{
    protected $fillable = ['company_id', 'document_type', 'file_path', 'is_verified'];

    protected $casts = [
        'is_verified' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function getSecureUrlAttribute(): string
    {
        return Storage::disk('private')->temporaryUrl($this->file_path, now()->addMinutes(15));
    }
}
