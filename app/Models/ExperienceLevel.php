<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExperienceLevel extends Model
{
    protected $fillable = [
        'name_ar',
        'name_en',
        'code',
        'min_years',
        'max_years',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'min_years'  => 'integer',
        'max_years'  => 'integer',
        'sort_order' => 'integer',
    ];

    public function userProfiles(): HasMany
    {
        return $this->hasMany(UserProfile::class, 'experience_level_id');
    }

    /**
     * Localization Accessor for Name based on app locale
     */
    public function getNameAttribute(): ?string
    {
        return app()->getLocale() === 'en'
            ? ($this->name_en ?? $this->name_ar)
            : ($this->name_ar ?? $this->name_en);
    }
}
