<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'slug',
        'title_ar',
        'title_en',
        'content_ar',
        'content_en',
        'meta_title',
        'meta_description',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
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
     * Dynamic localization accessor for content attribute based on app locale
     */
    public function getContentAttribute(): ?string
    {
        return app()->getLocale() === 'en' 
            ? ($this->content_en ?? $this->content_ar) 
            : ($this->content_ar ?? $this->content_en);
    }
}
