<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'country_id',
        'user_type',
        'status',
        'otp_code',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp_code',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // العلاقات Relationships
    public function country(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function candidateProfile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function company(): HasOne
    {
        return $this->hasOne(Company::class);
    }

    public function companyProfile(): HasOne
    {
        return $this->hasOne(Company::class);
    }

    public function countries(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Country::class, 'user_countries')->withPivot('type')->withTimestamps();
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function video(): HasOne
    {
        return $this->hasOne(Video::class);
    }

    public function otps(): HasMany
    {
        return $this->hasMany(Otp::class);
    }

    public function refreshTokens(): HasMany
    {
        return $this->hasMany(RefreshToken::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class, 'user_id');
    }

    public function bookmarkedCandidates(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'bookmarks', 'user_id', 'candidate_id')->withTimestamps();
    }

    public function isCandidateBookmarked(int $candidateUserId): bool
    {
        return $this->bookmarks()->where('candidate_id', $candidateUserId)->exists();
    }


    // Accessors & Helper Methods
    public function getDisplayNameAttribute(): ?string
    {
        return $this->name ?? $this->company?->company_name;
    }

    public function getIsCandidateAttribute(): bool
    {
        return $this->user_type === 'candidate';
    }

    public function getIsCompanyAttribute(): bool
    {
        return $this->user_type === 'company';
    }

    public function getIsAdminAttribute(): bool
    {
        return $this->user_type === 'admin';
    }

    public function isCandidate(): bool
    {
        return $this->user_type === 'candidate';
    }

    public function isCompany(): bool
    {
        return $this->user_type === 'company';
    }
}
