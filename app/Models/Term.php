<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'title_ar',
        'title_en',
        'desc_ar',
        'desc_en',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Scope to fetch active terms sorted by sort_order
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order', 'asc');
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

    /**
     * Dynamic localization accessor for desc attribute based on app locale
     */
    public function getDescAttribute(): ?string
    {
        return app()->getLocale() === 'en' 
            ? ($this->desc_en ?? $this->desc_ar) 
            : ($this->desc_ar ?? $this->desc_en);
    }

    /**
     * Alias for desc attribute
     */
    public function getDescriptionAttribute(): ?string
    {
        return $this->getDescAttribute();
    }
}
