<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Application extends Model
{
    protected $fillable = ['code', 'company_id', 'candidate_profile_id', 'status', 'notes'];

    protected static function booted(): void
    {
        static::creating(function (Application $application) {
            if (empty($application->code)) {
                $application->code = static::generateUniqueCode();
            }
        });
    }

    public static function generateUniqueCode(): string
    {
        do {
            $randomString = strtolower(Str::random(6));
            $code = 'Mkemet-' . $randomString;
        } while (static::where('code', $code)->exists());

        return $code;
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function candidateProfile(): BelongsTo
    {
        return $this->belongsTo(UserProfile::class, 'candidate_profile_id');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(ApplicationStatusHistory::class);
    }
}
