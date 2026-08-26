# الكود الكامل لطبقة الـ API والـ Resources والـ Controllers (مفصلة ومفككة)
## مشروع منصة أم كميت لإلحاق العمالة بالخارج (M-Kemet)

تحتوي هذه الوثيقة على **التحديث المعماري المفصل** لطبقة الـ API، بعد فصل بيانات الحساب الأساسية (`UserResource`) عن البيانات التكميلية (`CandidateProfileResource` و `CompanyProfileResource`) لإتاحة مرونة كاملة في الاستخدام فور التسجيل وأثناء استكمال البيانات.

---

## 1. الـ Trait الاستجابة الموحدة `ApiResponse`

### 📄 File: `app/Traits/ApiResponse.php`
```php
<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * إرجاع استجابة نجاح موحدة (Success Response)
     */
    public function successResponse($data = null, string $message = 'تمت العملية بنجاح', int $code = 200): JsonResponse
    {
        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => $data,
            'errors'  => null,
        ], $code);
    }

    /**
     * إرجاع استجابة خطأ موحدة (Error Response)
     */
    public function errorResponse(string $message = 'حدث خطأ ما', int $code = 400, $errors = null): JsonResponse
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
            'data'    => null,
            'errors'  => $errors,
        ], $code);
    }

    /**
     * استجابة عدم العثور على العنصر (404 Not Found)
     */
    public function notFoundResponse(string $message = 'العنصر غير موجود'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }

    /**
     * استجابة أخطاء التحقق من البيانات (422 Validation Error)
     */
    public function validationErrorResponse($errors, string $message = 'البيانات المدخلة غير صالحة'): JsonResponse
    {
        return $this->errorResponse($message, 422, $errors);
    }
}
```

---

## 2. الـ Eloquent Resources (مُفككة ومستقلة)

### 📄 Resource 1: `app/Http/Resources/UserResource.php`
* **الاستخدام**: تُستخدم فور **تسجيل الحساب الجديد (Register / Login / OTP)** لإرجاع بيانات الحساب الأساسية فقط.

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'user_type' => $this->user_type, // candidate, company, admin
            'account_status' => $this->status, // active, pending, suspended
            'email_verified' => !is_null($this->email_verified_at),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
```

---

### 📄 Resource 2: `app/Http/Resources/CandidateProfileResource.php`
* **الاستخدام**: تُستخدم في شاشات **تكملة الملف الشخصي والمهني** والميديا والمراجعة (تستدعي الـ Resources المنفصلة).

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CandidateProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'personal_info' => new CandidatePersonalInfoResource($this),
            'career_info' => new CandidateCareerInfoResource($this),
            'media' => new CandidateMediaResource($this),
            'profile_status' => $this->status, // pending, approved, rejected
            'rejection_reason' => $this->rejection_reason,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
```

---

### 📄 Resource 2.1: `app/Http/Resources/CandidatePersonalInfoResource.php`
* **الاستخدام**: تُستخدم لتنسيق البيانات الشخصية الخاصة بالمتقدم.

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CandidatePersonalInfoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'age' => $this->age,
            'gender' => $this->gender,
            'current_country' => new CountryResource($this->whenLoaded('currentCountry')),
        ];
    }
}
```

---

### 📄 Resource 2.2: `app/Http/Resources/CandidateCareerInfoResource.php`
* **الاستخدام**: تُستخدم لتنسيق البيانات المهنية والخبرات والدول والمهن المستهدفة.

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CandidateCareerInfoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'qualification' => $this->qualification,
            'experience_years' => $this->experience_years,
            'expected_salary' => $this->expected_salary,
            'willing_to_travel' => $this->willing_to_travel,
            'languages' => $this->languages ?? [],
            'summary' => $this->summary,
            'professions' => ProfessionResource::collection($this->whenLoaded('professions')),
            'target_countries' => CountryResource::collection($this->whenLoaded('targetCountries')),
        ];
    }
}
```

---

### 📄 Resource 2.3: `app/Http/Resources/CandidateMediaResource.php`
* **الاستخدام**: تُستخدم لتنسيق المستندات والميديا الخاصة بالمتقدم (الصورة، الـ CV، الجواز، الهوية، الفيديو التعريفى).

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CandidateMediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $this->user ?? null;

        return [
            'personal_photo' => $user?->documents?->where('document_type', 'personal_photo')->first()?->secure_url,
            'cv' => $user?->documents?->where('document_type', 'cv')->first()?->secure_url,
            'passport' => $user?->documents?->where('document_type', 'passport')->first()?->secure_url,
            'national_id' => $user?->documents?->where('document_type', 'national_id')->first()?->secure_url,
            'intro_video' => $user?->video ? new CandidateVideoResource($user->video) : null,
        ];
    }
}
```

---

### 📄 Resource 2.4: `app/Http/Resources/CandidateVideoResource.php`
* **الاستخدام**: تُستخدم لتنسيق بيانات الفيديو التعريفي للمتقدم.

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CandidateVideoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'video_url' => $this->video_url,
            'thumbnail_url' => $this->thumbnail_url,
            'duration_seconds' => $this->duration_seconds,
            'status' => $this->status,
        ];
    }
}
```

---

### 📄 Resource 3: `app/Http/Resources/CompanyProfileResource.php`
* **الاستخدام**: تُستخدم في **شاشات استكمال ملف الشركة والتراخيص التجارية** (تستدعي الـ Resources المنفصلة).

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'company_info' => new CompanyInfoResource($this),
            'documents' => CompanyDocumentResource::collection($this->whenLoaded('documents')),
            'status' => $this->status, // pending, approved, rejected
            'rejection_reason' => $this->rejection_reason,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
```

---

### 📄 Resource 3.1: `app/Http/Resources/CompanyInfoResource.php`
* **الاستخدام**: تُستخدم لتنسيق البيانات الأساسية الخاصة بالشركة (الاسم، السجل التجاري، المجال، الدولة والمدينة والعنوان).

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyInfoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'company_name' => $this->company_name,
            'commercial_register_number' => $this->commercial_register_number,
            'industry' => $this->industry,
            'country' => new CountryResource($this->whenLoaded('country')),
            'city' => $this->city,
            'address' => $this->address,
        ];
    }
}
```

---

### 📄 Resource 3.2: `app/Http/Resources/CompanyDocumentResource.php`
* **الاستخدام**: تُستخدم لتنسيق التراخيص والمستندات الورقية الخاصة بالشركة وتحديد حالة التحقق منها.

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'document_type' => $this->document_type,
            'secure_url' => $this->secure_url,
            'is_verified' => $this->is_verified,
        ];
    }
}
```

---

### 📄 Resource 4: `app/Http/Resources/CountryResource.php`
* **الاستخدام**: لعرض **قائمة الدول المستهدفة والدولة الحالية** في تطبيق الموبايل والشركة.

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name, // Accessor ديناميكي يعتمد على لغة التطبيق الحالية
            'code'      => $this->code,
            'flag_url'  => $this->flag_url,
            'is_active' => $this->is_active,
        ];
    }
}
```

---

### 📄 Resource 5: `app/Http/Resources/ProfessionResource.php`
* **الاستخدام**: لعرض **قائمة المهن والتصنيفات (سباك، شيف، كهربائي...)** مترجمة حسب لغة الطلب.

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'title'     => $this->title, // Accessor ديناميكي يعتمد على لغة التطبيق الحالية
            'category'  => $this->category,
            'is_active' => $this->is_active,
        ];
    }
}
```

---

### 📄 Resource 6: `app/Http/Resources/SettingResource.php`
* **الاستخدام**: لعرض **إعدادات التطبيق والتواصل والروابط**.

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'key'         => $this->key,
            'value'       => $this->value,
            'group'       => $this->group,
            'type'        => $this->type,
            'description' => $this->description,
        ];
    }
}
```

---

### 📄 Resource 7: `app/Http/Resources/PageResource.php`
* **الاستخدام**: لعرض **صفحات السياسات، الشروط والأحكام، وعن التطبيق** مترجمة حسب لغة الطلب.

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'slug'             => $this->slug,
            'title'            => $this->title, // Accessor ديناميكي يعتمد على لغة التطبيق الحالية
            'content'          => $this->content, // Accessor ديناميكي يعتمد على لغة التطبيق الحالية
            'meta_title'       => $this->meta_title,
            'meta_description' => $this->meta_description,
        ];
    }
}
```
