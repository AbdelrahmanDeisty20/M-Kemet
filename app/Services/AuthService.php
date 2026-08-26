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

use App\Models\UserProfile;
use Illuminate\Support\Facades\DB;

class AuthService
{
    use ApiResponse;

    /**
     * تسجيل حساب جديد وتوليد رمز OTP (العام / الشركات)
     */
    public function register(array $data): JsonResponse
    {
        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'phone'     => $data['phone'],
            'password'  => Hash::make($data['password']),
            'user_type' => 'company',
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
            'user'    => new UserResource($user),
            'profile' => $user->companyProfile ? new CompanyProfileResource($user->companyProfile) : null,
        ], __('messages.accountCreatedSuccessfully'), 201);
    }

    /**
     * تسجيل حساب جديد للباحث عن عمل (Candidate Registration)
     */
    public function registerCandidate(array $data): JsonResponse
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name'      => $data['name'],
                'email'     => $data['email'],
                'phone'     => $data['phone'],
                'password'  => Hash::make($data['password']),
                'user_type' => 'candidate',
                'status'    => 'pending',
            ]);

            // إنشاء ملف الباحث عن عمل
            $profile = UserProfile::create([
                'user_id'            => $user->id,
                'current_country_id' => $data['current_country_id'],
                'birth_date'         => $data['birth_date'],
                'gender'             => $data['gender'],
                'status'             => 'pending',
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

            Mail::to($user->email)->queue(new OtpMail($code, $user->name));

            $profile->load('currentCountry');

            return $this->successResponse([
                'user'    => new UserResource($user),
            ], __('messages.accountCreatedSuccessfully'), 201);
        });
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
            return $this->errorResponse(__('messages.otp_invalid'), 400);
        }

        if ($otp->expires_at < now()) {
            return $this->errorResponse(__('messages.otp_expired'), 400);
        }

        if (!Hash::check($data['code'], $otp->code)) {
            return $this->errorResponse(__('messages.otp_invalid'), 400);
        }

        $user = User::where('email', $data['email'])->first();
        if (!$user) {
            return $this->notFoundResponse(__('messages.user_not_found'));
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
        ], __('messages.otpVerifiedSuccessfully'));
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
            return $this->errorResponse(__('messages.otp_wait_resend'), 429);
        }

        $user = User::where('email', $data['email'])->first();

        if ($user->email_verified_at) {
            return $this->errorResponse(__('messages.userAlreadyActive'), 400);
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

        return $this->successResponse(null, __('messages.otpResentSuccessfully'));
    }

    /**
     * تسجيل الدخول
     */
    public function login(array $data): JsonResponse
    {
        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return $this->errorResponse(__('messages.invalidCredentials'), 401);
        }

        if ($user->status === 'suspended') {
            return $this->errorResponse(__('messages.userSuspended'), 403);
        }

        if (is_null($user->email_verified_at)) {
            return $this->errorResponse(__('messages.userPendingVerification'), 403);
        }

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
        ], __('messages.operationSuccessful'));
    }

    /**
     * تجديد التوكن المنتهي عبر Refresh Token
     */
    public function refreshToken(string $plainRefreshToken): JsonResponse
    {
        $hashedToken = hash('sha256', $plainRefreshToken);

        $refreshToken = RefreshToken::where('token', $hashedToken)->first();

        if (!$refreshToken || $refreshToken->expires_at < now()) {
            return $this->errorResponse(__('messages.invalid_token'), 401);
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
        ], __('messages.tokenRefreshedSuccessfully'));
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
        ], __('messages.profileSuccessFully'));
    }

    /**
     * تسجيل الخروج من الجهاز الحالي
     */
    public function logout(): JsonResponse
    {
        $user = Auth::user();
        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return $this->successResponse(null, __('messages.logoutSuccessFully'));
    }

    /**
     * تسجيل الخروج من جميع الأجهزة
     */
    public function logoutAllDevices(): JsonResponse
    {
        $user = Auth::user();
        if ($user) {
            $user->tokens()->delete();
            RefreshToken::where('user_id', $user->id)->delete();
        }

        return $this->successResponse(null, __('messages.logoutAllDevicesSuccessFully'));
    }
}
