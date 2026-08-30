<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'birth_date',
        'gender',
        'gender_id',
        'current_country_id',
        'qualification',
        'experience_years',
        'expected_salary',
        'willing_to_travel',
        'languages',
        'summary',
        'status',
        'rejection_reason',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'willing_to_travel' => 'boolean',
        'languages' => 'array',
        'expected_salary' => 'decimal:2',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function gender(): BelongsTo
    {
        return $this->belongsTo(Gender::class);
    }

    public function currentCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'current_country_id');
    }

    public function targetCountries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class, 'user_target_countries');
    }

    public function professions(): BelongsToMany
    {
        return $this->belongsToMany(Profession::class, 'user_professions');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'candidate_profile_id');
    }

    // Accessors
    public function getAgeAttribute(): ?int
    {
        return $this->birth_date ? Carbon::parse($this->birth_date)->age : null;
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeFilterCandidates($query, array $filters)
    {
        if (!empty($filters['country_id'])) {
            $query->whereHas('targetCountries', function ($q) use ($filters) {
                $q->where('countries.id', $filters['country_id']);
            });
        }

        if (!empty($filters['profession_id'])) {
            $query->whereHas('professions', function ($q) use ($filters) {
                $q->where('professions.id', $filters['profession_id']);
            });
        }

        if (!empty($filters['min_experience'])) {
            $query->where('experience_years', '>=', $filters['min_experience']);
        }

        return $query;
    }
}
