<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'birth_date',
        'gender_id',
        'current_country_id',
        'qualification_id',
        'qualification',
        'sub_specialization',
        'profession_id',
        'experience_years',
        'experience_level_id',
        'expected_salary',
        'willing_to_travel',
        'languages',
        'skills',
        'summary',
        'status',
        'rejection_reason',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'willing_to_travel' => 'boolean',
        'languages' => 'array',
        'skills' => 'array',
        'expected_salary' => 'decimal:2',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function gender(): BelongsTo
    {
        return $this->belongsTo(Gender::class, 'gender_id');
    }

    public function genderRelation(): BelongsTo
    {
        return $this->belongsTo(Gender::class, 'gender_id');
    }


    public function currentCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'current_country_id');
    }

    public function qualification(): BelongsTo
    {
        return $this->belongsTo(Qualification::class, 'qualification_id');
    }

    public function experienceLevel(): BelongsTo
    {
        return $this->belongsTo(ExperienceLevel::class, 'experience_level_id');
    }

    public function targetCountries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class, 'user_target_countries');
    }

    public function professions(): BelongsToMany
    {
        return $this->belongsToMany(Profession::class, 'user_professions');
    }

    public function profession(): BelongsTo
    {
        return $this->belongsTo(Profession::class, 'profession_id');
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

    public function getCompletionPercentageAttribute(): int
    {
        $totalSteps = 10;
        $completedSteps = 0;

        if (!empty($this->qualification_id) || !empty($this->qualification)) $completedSteps++;
        if (!empty($this->sub_specialization)) $completedSteps++;
        if ($this->experience_years > 0 || !empty($this->experience_level_id)) $completedSteps++;
        if (!empty($this->summary)) $completedSteps++;
        if (!empty($this->expected_salary)) $completedSteps++;
        if (!empty($this->languages) && count($this->languages) > 0) $completedSteps++;
        if (!empty($this->skills) && count($this->skills) > 0) $completedSteps++;
        if (!empty($this->profession_id) || $this->professions()->exists()) $completedSteps++;
        if ($this->targetCountries()->exists()) $completedSteps++;
        if ($this->user && $this->user->documents()->where('document_type', 'cv')->exists()) $completedSteps++;

        return (int) round(($completedSteps / $totalSteps) * 100);
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
            $query->where(function ($q) use ($filters) {
                $q->where('profession_id', $filters['profession_id'])
                  ->orWhereHas('professions', function ($p) use ($filters) {
                      $p->where('professions.id', $filters['profession_id']);
                  });
            });
        }

        if (!empty($filters['experience_level_id'])) {
            $query->where('experience_level_id', $filters['experience_level_id']);
        }

        if (!empty($filters['min_experience'])) {
            $query->where('experience_years', '>=', $filters['min_experience']);
        }

        return $query;
    }
}
