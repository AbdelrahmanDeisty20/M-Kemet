# الهيكل المعماري الشامل لنظام المصادقة (Auth System Architecture)
## مشروع منصة أم كميت لإلحاق العمالة بالخارج (M-Kemet)

توضح هذه الوثيقة **التصميم والتطبيق المعماري الكامل لنظام المصادقة (Authentication & Authorization)** المتبع في منصة M-Kemet، والمستوحى من الأنماط الاحترافية بـ `KCODE` (مبدأ Service Layer Pattern + Sanctum Tokens + Refresh Tokens + Mapped Resources + OTP System).

---

## 📌 1. النمط المعماري والخصائص الرئيسية (Architecture Overview)

1. **طبقة الخدمات (Service Layer Pattern):**
   - فصل منطق العمليات والمعالجة عن الـ Controller بالكامل وإيداعها في `AuthService` و `PasswordResetService`.
2. **إدارة الجلسات والأمان (Sanctum + Refresh Token):**
   - إصدار `auth_token` قصير الأجل عبر Laravel Sanctum للتحقق المباشر من الـ APIs.
   - إصدار `refresh_token` طويل الأجل (صالح لمدة 7 أيام) ومُشفر بنظام `SHA-256` في جدول مستقل لتجديد الجلسة دون الحاجة لطلب كلمة المرور مجدداً.
3. **نظام الـ OTP المشفّر لحماية التسجيل والاستعادة:**
   - تشفير الرموز في قاعدة البيانات باستخدام `Hash::make()`.
   - صلاحية الرمز 5 دقائق فقط.
   - تطبيق حماية من التكرار والضغط (Rate Limiting) بإلزام الانتظار **دقيقة واحدة** بين كل طلب إعادة إرسال.
   - إرسال الإيميلات عبر طوابير الانتظار `Mail::queue()`.
4. **دعم تعدد أنواع الحسابات (Multi-Role Support):**
   - تمييز الحسابات بين مرشحي عمالة (`candidate`) وشركات استقدام/عمل (`company`) وأدمن (`admin`).
5. **استجابات موحدة (Unified API Responses):**
   - الاعتماد على `ApiResponse` Trait للرد بـ JSON موحد ومدمج مع `UserResource` و `CandidateProfileResource` و `CompanyProfileResource`.

---

## 🗄️ 2. مخطط قواعد البيانات (Database Migrations)

### 📄 Migration 1: `database/migrations/2026_08_25_000001_create_otps_table.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('email')->index();
            $table->string('phone')->nullable()->index();
            $table->string('code'); // Hashed Code
            $table->enum('type', ['register', 'reset_password']);
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otps');
    }
};
```

---

### 📄 Migration 2: `database/migrations/2026_08_25_000002_create_refresh_tokens_table.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refresh_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('token', 64)->unique(); // SHA-256 Hashed
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refresh_tokens');
    }
};
```

---

## 📋 3. طبقة التحقق من البيانات (Form Requests)

### 📄 Request 1: `app/Http/Requests/API/Auth/RegisterRequest.php`
```php
<?php

namespace App\Http\Requests\API\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone'     => ['required', 'string', 'max:20', 'unique:users,phone'],
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
            'user_type' => ['required', 'string', 'in:candidate,company'],
        ];
    }
}
```

---

### 📄 Request 2: `app/Http/Requests/API/Auth/LoginRequest.php`
```php
<?php

namespace App\Http\Requests\API\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }
}
```

---

### 📄 Request 3: `app/Http/Requests/API/Auth/VerifyOtpRequest.php`
```php
<?php

namespace App\Http\Requests\API\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'code'  => ['required', 'string', 'size:6'],
        ];
    }
}
```

---

### 📄 Request 4: `app/Http/Requests/API/Auth/ResendOtpRequest.php`
```php
<?php

namespace App\Http\Requests\API\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ResendOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'exists:users,email'],
        ];
    }
}
```

---

### 📄 Request 5: `app/Http/Requests/API/Auth/RefreshTokenRequest.php`
```php
<?php

namespace App\Http\Requests\API\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RefreshTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'refresh_token' => ['required', 'string'],
        ];
    }
}
```

---

### 📄 Request 6: `app/Http/Requests/API/Auth/ResetPasswordRequest.php`
```php
<?php

namespace App\Http\Requests\API\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email', 'exists:users,email'],
            'code'     => ['required', 'string', 'size:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
```

---

## ⚙️ 4. طبقة الخدمات (Services Layer)

### 📄 Service 1: `app/Services/AuthService.php`
```php
<?php

namespace App\Services;

use App\Http\Resources\UserResource;
use App\Http\Resources\CandidateProfileResource;
use App\Http\Resources\CompanyProfileResource;
use App\Mail\OtpMail;
use App\Models\Otp;
use App\Models\RefreshToken;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    use ApiResponse;

    /**
     * تسجيل حساب جديد وتوليد رمز OTP
     */
    public function register(array $data): JsonResponse
    {
        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'phone'     => $data['phone'],
            'password'  => Hash::make($data['password']),
            'user_type' => $data['user_type'],
            'status'    => 'pending',
        ]);

        $code = (string) random_int(100000, 999999);
        
        Otp::create([
            'user_id'    => $user->id,
            'email'      => $user->email,
            'phone'      => $user->phone,
            'code'       => Hash::make($code),
            'type'       => 'register',
            'expires_at' => now()->addMinutes(5),
        ]);

        // إرسال الإيميل عبر الـ Queue
        Mail::to($user->email)->queue(new OtpMail($code, $user->name));

        return $this->successResponse([
            'email' => $user->email,
        ], 'تم إنشاء الحساب بنجاح، يُرجى إدخال رمز التحقق (OTP) المكون من 6 أرقام المرسل إلى بريدك الإلكتروني', 201);
    }

    /**
     * تفعيل الحساب عبر OTP وتوليد Tokens
     */
    public function verifyOtp(array $data): JsonResponse
    {
        $otp = Otp::where('email', $data['email'])
            ->where('type', 'register')
            ->latest()
            ->first();

        if (!$otp) {
            return $this->errorResponse('رمز التحقق غير صحيح أو غير موجود', 400);
        }

        if ($otp->expires_at < now()) {
            return $this->errorResponse('انتهت صلاحية رمز التحقق، يرجى طلب رمز جديد', 400);
        }

        if (!Hash::check($data['code'], $otp->code)) {
            return $this->errorResponse('رمز التحقق غير صحيح', 400);
        }

        $user = User::where('email', $data['email'])->first();
        if (!$user) {
            return $this->notFoundResponse('المستخدم غير موجود');
        }

        $user->email_verified_at = now();
        $user->status = 'active';
        $user->save();

        // حذف رموز OTP المستخدمة
        Otp::where('email', $data['email'])->where('type', 'register')->delete();

        // توليد Tokens
        $authToken = $user->createToken('auth_token')->plainTextToken;
        $plainRefreshToken = Str::random(64);

        RefreshToken::create([
            'user_id'    => $user->id,
            'token'      => hash('sha256', $plainRefreshToken),
            'expires_at' => Carbon::now()->addDays(7),
        ]);

        return $this->successResponse([
            'user'          => new UserResource($user),
            'auth_token'    => $authToken,
            'refresh_token' => $plainRefreshToken,
        ], 'تم تفعيل الحساب وتسجيل الدخول بنجاح');
    }

    /**
     * إعادة إرسال رمز OTP مع حماية Rate Limit (دقيقة واحدة)
     */
    public function resendOtp(array $data): JsonResponse
    {
        $latestOtp = Otp::where('email', $data['email'])
            ->where('type', 'register')
            ->latest()
            ->first();

        if ($latestOtp && $latestOtp->created_at->addMinute() > now()) {
            return $this->errorResponse('يرجى الانتظار دقيقة واحدة قبل طلب رمز جديد', 429);
        }

        $user = User::where('email', $data['email'])->first();

        if ($user->email_verified_at) {
            return $this->errorResponse('هذا البريد الإلكتروني مفعل بالفعل', 400);
        }

        Otp::where('email', $data['email'])->where('type', 'register')->delete();

        $code = (string) random_int(100000, 999999);

        Otp::create([
            'user_id'    => $user->id,
            'email'      => $user->email,
            'phone'      => $user->phone,
            'code'       => Hash::make($code),
            'type'       => 'register',
            'expires_at' => now()->addMinutes(5),
        ]);

        Mail::to($user->email)->queue(new OtpMail($code, $user->name));

        return $this->successResponse(null, 'تم إعادة إرسال رمز التحقق بنجاح');
    }

    /**
     * تسجيل الدخول
     */
    public function login(array $data): JsonResponse
    {
        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return $this->errorResponse('البريد الإلكتروني أو كلمة المرور غير صحيحة', 401);
        }

        if ($user->status === 'suspended') {
            return $this->errorResponse('هذا الحساب معطل، يرجى التواصل مع الدعم الفني', 403);
        }

        if (is_null($user->email_verified_at)) {
            return $this->errorResponse('الحساب غير مفعل، يرجى تفعيل الحساب عبر رمز OTP أولاً', 403);
        }

        $authToken = $user->createToken('auth_token')->plainTextToken;
        $plainRefreshToken = Str::random(64);

        RefreshToken::create([
            'user_id'    => $user->id,
            'token'      => hash('sha256', $plainRefreshToken),
            'expires_at' => Carbon::now()->addDays(7),
        ]);

        $profile = match($user->user_type) {
            'candidate' => $user->candidateProfile ? new CandidateProfileResource($user->candidateProfile) : null,
            'company'   => $user->companyProfile ? new CompanyProfileResource($user->companyProfile) : null,
            default     => null,
        };

        return $this->successResponse([
            'user'          => new UserResource($user),
            'profile'       => $profile,
            'auth_token'    => $authToken,
            'refresh_token' => $plainRefreshToken,
        ], 'تم تسجيل الدخول بنجاح');
    }

    /**
     * تجديد التوكن المنتهي عبر Refresh Token
     */
    public function refreshToken(string $plainRefreshToken): JsonResponse
    {
        $hashedToken = hash('sha256', $plainRefreshToken);

        $refreshToken = RefreshToken::where('token', $hashedToken)->first();

        if (!$refreshToken || $refreshToken->expires_at < now()) {
            return $this->errorResponse('الـ Refresh Token غير صالح أو منتهي الصلاحية', 401);
        }

        $user = $refreshToken->user;

        // حذف التوكنات القديمة
        $user->tokens()->delete();
        $refreshToken->delete();

        // إنشاء توكنات جديدة
        $newAuthToken = $user->createToken('auth_token')->plainTextToken;
        $newPlainRefreshToken = Str::random(64);

        RefreshToken::create([
            'user_id'    => $user->id,
            'token'      => hash('sha256', $newPlainRefreshToken),
            'expires_at' => Carbon::now()->addDays(7),
        ]);

        return $this->successResponse([
            'auth_token'    => $newAuthToken,
            'refresh_token' => $newPlainRefreshToken,
        ], 'تم تجديد التوكن بنجاح');
    }

    /**
     * عرض البروفايل الحالي
     */
    public function profile(): JsonResponse
    {
        $user = Auth::user();

        $profile = match($user->user_type) {
            'candidate' => $user->candidateProfile ? new CandidateProfileResource($user->candidateProfile) : null,
            'company'   => $user->companyProfile ? new CompanyProfileResource($user->companyProfile) : null,
            default     => null,
        };

        return $this->successResponse([
            'user'    => new UserResource($user),
            'profile' => $profile,
        ], 'تم جلب بيانات البروفايل بنجاح');
    }

    /**
     * تسجيل الخروج من الجهاز الحالي
     */
    public function logout(): JsonResponse
    {
        $user = Auth::user();
        $user->currentAccessToken()->delete();

        return $this->successResponse(null, 'تم تسجيل الخروج بنجاح');
    }

    /**
     * تسجيل الخروج من جميع الأجهزة
     */
    public function logoutAllDevices(): JsonResponse
    {
        $user = Auth::user();
        $user->tokens()->delete();
        RefreshToken::where('user_id', $user->id)->delete();

        return $this->successResponse(null, 'تم تسجيل الخروج من كافة الأجهزة بنجاح');
    }
}
```

---

### 📄 Service 2: `app/Services/PasswordResetService.php`
```php
<?php

namespace App\Services;

use App\Mail\OtpMail;
use App\Models\Otp;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class PasswordResetService
{
    use ApiResponse;

    /**
     * إرسال رمز OTP لإعادة تعيين كلمة المرور
     */
    public function sendResetOtp(string $email): JsonResponse
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return $this->notFoundResponse('البريد الإلكتروني غير مسجل بالمنظومة');
        }

        // مسح الرموز القديمة لنفس الغرض
        Otp::where('email', $email)->where('type', 'reset_password')->delete();

        $code = (string) random_int(100000, 999999);

        Otp::create([
            'user_id'    => $user->id,
            'email'      => $user->email,
            'phone'      => $user->phone,
            'code'       => Hash::make($code),
            'type'       => 'reset_password',
            'expires_at' => now()->addMinutes(5),
        ]);

        Mail::to($user->email)->queue(new OtpMail($code, $user->name));

        return $this->successResponse(null, 'تم إرسال رمز إعادة تعيين كلمة المرور إلى بريدك الإلكتروني');
    }

    /**
     * التحقق من رمز OTP الخاص بنسيان كلمة المرور
     */
    public function verifyResetOtp(string $email, string $code): JsonResponse
    {
        $otp = Otp::where('email', $email)
            ->where('type', 'reset_password')
            ->latest()
            ->first();

        if (!$otp || $otp->expires_at < now() || !Hash::check($code, $otp->code)) {
            return $this->errorResponse('رمز التحقق غير صحيح أو منتهي الصلاحية', 400);
        }

        return $this->successResponse(null, 'رمز التحقق صحيح، يمكنك الآن إدخال كلمة المرور الجديدة');
    }

    /**
     * إعادة تعيين كلمة المرور
     */
    public function resetPassword(array $data): JsonResponse
    {
        $otp = Otp::where('email', $data['email'])
            ->where('type', 'reset_password')
            ->latest()
            ->first();

        if (!$otp || $otp->expires_at < now() || !Hash::check($data['code'], $otp->code)) {
            return $this->errorResponse('رمز التحقق غير صحيح أو منتهي الصلاحية', 400);
        }

        $user = User::where('email', $data['email'])->first();
        if (!$user) {
            return $this->notFoundResponse('المستخدم غير موجود');
        }

        $user->password = Hash::make($data['password']);
        $user->save();

        // مسح الـ OTP وإبطال كل الجلسات السابقة للأمان
        Otp::where('email', $data['email'])->where('type', 'reset_password')->delete();
        $user->tokens()->delete();

        return $this->successResponse(null, 'تم تغيير كلمة المرور بنجاح، يمكنك الآن تسجيل الدخول');
    }
}
```

---

## 🎮 5. طبقة المتحكمات (Controllers Layer)

### 📄 Controller 1: `app/Http/Controllers/API/Auth/AuthController.php`
```php
<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\LoginRequest;
use App\Http\Requests\API\Auth\RefreshTokenRequest;
use App\Http\Requests\API\Auth\RegisterRequest;
use App\Http\Requests\API\Auth\ResendOtpRequest;
use App\Http\Requests\API\Auth\VerifyOtpRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        return $this->authService->register($request->validated());
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        return $this->authService->verifyOtp($request->validated());
    }

    public function resendOtp(ResendOtpRequest $request): JsonResponse
    {
        return $this->authService->resendOtp($request->validated());
    }

    public function login(LoginRequest $request): JsonResponse
    {
        return $this->authService->login($request->validated());
    }

    public function refreshToken(RefreshTokenRequest $request): JsonResponse
    {
        return $this->authService->refreshToken($request->validated()['refresh_token']);
    }

    public function profile(): JsonResponse
    {
        return $this->authService->profile();
    }

    public function logout(): JsonResponse
    {
        return $this->authService->logout();
    }

    public function logoutAllDevices(): JsonResponse
    {
        return $this->authService->logoutAllDevices();
    }
}
```

---

### 📄 Controller 2: `app/Http/Controllers/API/Auth/PasswordResetController.php`
```php
<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\ResendOtpRequest;
use App\Http\Requests\API\Auth\ResetPasswordRequest;
use App\Http\Requests\API\Auth\VerifyOtpRequest;
use App\Services\PasswordResetService;
use Illuminate\Http\JsonResponse;

class PasswordResetController extends Controller
{
    protected PasswordResetService $passwordResetService;

    public function __construct(PasswordResetService $passwordResetService)
    {
        $this->passwordResetService = $passwordResetService;
    }

    public function sendResetOtp(ResendOtpRequest $request): JsonResponse
    {
        return $this->passwordResetService->sendResetOtp($request->validated()['email']);
    }

    public function verifyResetOtp(VerifyOtpRequest $request): JsonResponse
    {
        $data = $request->validated();
        return $this->passwordResetService->verifyResetOtp($data['email'], $data['code']);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        return $this->passwordResetService->resetPassword($request->validated());
    }
}
```

---

## 🛣️ 6. ملف المسارات والروابط (API Routes)

### 📄 `routes/api.php`
```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\Auth\PasswordResetController;

/*
|--------------------------------------------------------------------------
| API Routes - V1 Authentication
|--------------------------------------------------------------------------
*/

Route::prefix('v1/auth')->group(function () {
    
    // المسارات العامة (Public Routes)
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);

    // نسيان واستعادة كلمة المرور
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetOtp']);
    Route::post('/verify-reset-otp', [PasswordResetController::class, 'verifyResetOtp']);
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);

    // المسارات المحمية بـ Sanctum (Protected Routes)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAllDevices']);
    });
});
```
