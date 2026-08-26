<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Country extends Model
{
    protected $fillable = ['name_ar', 'name_en', 'code', 'flag_icon_path', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function candidates(): HasMany
    {
        return $this->hasMany(UserProfile::class, 'current_country_id');
    }

    public function getFlagUrlAttribute(): ?string
    {
        return $this->flag_icon_path ? Storage::disk('public')->url($this->flag_icon_path) : null;
    }

    /**
     * Dynamic localization accessor for name attribute based on app locale
     */
    public function getNameAttribute(): ?string
    {
        return app()->getLocale() === 'en' 
            ? ($this->name_en ?? $this->name_ar) 
            : ($this->name_ar ?? $this->name_en);
    }
}
