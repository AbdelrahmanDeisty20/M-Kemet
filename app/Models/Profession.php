<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Profession extends Model
{
    protected $fillable = ['title_ar', 'title_en', 'category', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function candidates(): BelongsToMany
    {
        return $this->belongsToMany(UserProfile::class, 'user_professions');
    }

    /**
     * Dynamic localization accessor for title attribute based on app locale
     */
    public function getTitleAttribute(): ?string
    {
        return app()->getLocale() === 'en' 
            ? ($this->title_en ?? $this->title_ar) 
            : ($this->title_ar ?? $this->title_en);
    }
}
