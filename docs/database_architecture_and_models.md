# الكود الكامل لملفات Migrations والنماذج Models
## مشروع منصة أم كميت لإلحاق العمالة بالخارج (M-Kemet)

تحتوي هذه الوثيقة على **أكواد PHP الكاملة والقابلة للتنفيذ** لجميع ملفات الـ Migrations وقواعد البيانات والنماذج (Models)، متضمنة العلاقات (Relationships)، المعالجات (Accessors & Mutators)، والنطاقات الاستعلامية (Scopes).

---

## 1. جدول الحسابات `users`

### 📄 Migration: `create_users_table.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->unique();
            $table->enum('user_type', ['candidate', 'company', 'admin'])->default('candidate');
            $table->enum('status', ['active', 'pending', 'suspended'])->default('active');
            $table->string('otp_code')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
```

### 📄 Model: `app/Models/User.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
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
            'password' => 'hashed',
        ];
    }

    // العلاقات Relationships
    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function company(): HasOne
    {
        return $this->hasOne(Company::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function video(): HasOne
    {
        return $this->hasOne(Video::class);
    }

    // Accessors
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
}
```

---

## 2. جدول الدول `countries`

### 📄 Migration: `create_countries_table.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar');
            $table->string('name_en');
            $table->string('code', 5)->unique(); // e.g., SA, AE, KW
            $table->string('flag_icon_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
```

### 📄 Model: `app/Models/Country.php`
```php
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
}
```

---

## 3. جدول المهن `professions`

### 📄 Migration: `create_professions_table.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('professions', function (Blueprint $table) {
            $table->id();
            $table->string('title_ar');
            $table->string('title_en')->nullable();
            $table->string('category')->default('عام'); // e.g. تشييد وبناء، ضيافة، هندسة
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('professions');
    }
};
```

### 📄 Model: `app/Models/Profession.php`
```php
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
}
```

---

## 4. جدول بيانات الباحثين `user_profiles` والجداول الوسيطة

### 📄 Migration: `create_user_profiles_table.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['male', 'female'])->default('male');
            $table->foreignId('current_country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->string('qualification')->nullable(); // المؤهل الدراسي
            $table->integer('experience_years')->default(0);
            $table->decimal('expected_salary', 10, 2)->nullable();
            $table->boolean('willing_to_travel')->default(true);
            $table->json('languages')->nullable(); // e.g. ["العربية", "الإنجليزية"]
            $table->text('summary')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });

        // Pivot Table: الباحث والمهن
        Schema::create('user_professions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('profession_id')->constrained()->cascadeOnDelete();
        });

        // Pivot Table: الباحث والدول المستهدفة للسفر
        Schema::create('user_target_countries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_target_countries');
        Schema::dropIfExists('user_professions');
        Schema::dropIfExists('user_profiles');
    }
};
```

### 📄 Model: `app/Models/UserProfile.php`
```php
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
```

---

## 5. جدول الشركات `companies` ومستنداتها

### 📄 Migration: `create_companies_table.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('company_name');
            $table->string('commercial_register_number')->nullable();
            $table->string('industry')->nullable();
            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $table->string('city')->nullable();
            $table->text('address')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('company_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('document_type'); // e.g. commercial_register, license
            $table->string('file_path');
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_documents');
        Schema::dropIfExists('companies');
    }
};
```

### 📄 Model: `app/Models/Company.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'user_id',
        'company_name',
        'commercial_register_number',
        'industry',
        'country_id',
        'city',
        'address',
        'status',
        'rejection_reason',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(CompanyDocument::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
```

### 📄 Model: `app/Models/CompanyDocument.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CompanyDocument extends Model
{
    protected $fillable = ['company_id', 'document_type', 'file_path', 'is_verified'];

    protected $casts = [
        'is_verified' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function getSecureUrlAttribute(): string
    {
        return Storage::disk('private')->temporaryUrl($this->file_path, now()->addMinutes(15));
    }
}
```

---

## 6. جدول المستندات الحساسة `documents`

### 📄 Migration: `create_documents_table.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('document_type', ['national_id', 'passport', 'cv', 'personal_photo']);
            $table->string('file_path');
            $table->string('disk')->default('private');
            $table->boolean('is_approved')->default(false);
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
```

### 📄 Model: `app/Models/Document.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    protected $fillable = ['user_id', 'document_type', 'file_path', 'disk', 'is_approved', 'rejection_reason'];

    protected $casts = [
        'is_approved' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getSecureUrlAttribute(): string
    {
        if ($this->document_type === 'personal_photo') {
            return Storage::disk('public')->url($this->file_path);
        }

        return Storage::disk($this->disk ?? 'private')->temporaryUrl(
            $this->file_path,
            now()->addMinutes(15)
        );
    }
}
```

---

## 7. جدول الفيديوهات `videos`

### 📄 Migration: `create_videos_table.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('video_path');
            $table->string('thumbnail_path')->nullable();
            $table->integer('duration_seconds')->default(0);
            $table->decimal('file_size_mb', 8, 2)->default(0);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
```

### 📄 Model: `app/Models/Video.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Video extends Model
{
    protected $fillable = [
        'user_id',
        'video_path',
        'thumbnail_path',
        'duration_seconds',
        'file_size_mb',
        'status',
        'rejection_reason',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getVideoUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->video_path);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail_path ? Storage::disk('public')->url($this->thumbnail_path) : null;
    }
}
```

---

## 8. جداول الطلبات والتايم لاين `applications`

### 📄 Migration: `create_applications_table.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('candidate_profile_id')->constrained('user_profiles')->cascadeOnDelete();
            $table->enum('status', ['pending', 'accepted', 'rejected', 'completed'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('application_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_status_histories');
        Schema::dropIfExists('applications');
    }
};
```

### 📄 Model: `app/Models/Application.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    protected $fillable = ['company_id', 'candidate_profile_id', 'status', 'notes'];

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
```

### 📄 Model: `app/Models/ApplicationStatusHistory.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationStatusHistory extends Model
{
    protected $fillable = ['application_id', 'status', 'changed_by_user_id', 'notes'];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
```

---

## 9. جدول الإعدادات العامة `settings`

### 📄 Migration: `create_settings_table.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->string('type')->default('text');
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
```

### 📄 Model: `app/Models/Setting.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group', 'type', 'description'];

    public static function get(string $key, $default = null)
    {
        return Cache::rememberForever("setting.{$key}", function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    public static function set(string $key, $value, string $group = 'general', string $type = 'text'): self
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group, 'type' => $type]
        );

        Cache::forget("setting.{$key}");
        return $setting;
    }
}
```

---

## 10. جدول الصفحات الديناميكية `pages`

### 📄 Migration: `create_pages_table.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title_ar');
            $table->string('title_en')->nullable();
            $table->longText('content_ar')->nullable();
            $table->longText('content_en')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
```

### 📄 Model: `app/Models/Page.php`
```php
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
}
```
