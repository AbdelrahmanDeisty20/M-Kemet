<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gender extends Model
{
    protected $fillable = ['name_ar', 'name_en', 'code', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function userProfiles(): HasMany
    {
        return $this->hasMany(UserProfile::class, 'gender_id');
    }

    /**
     * Localization Accessor for Name
     */
    public function getNameAttribute(): ?string
    {
        return app()->getLocale() === 'en' 
            ? ($this->name_en ?? $this->name_ar) 
            : ($this->name_ar ?? $this->name_en);
    }
}
