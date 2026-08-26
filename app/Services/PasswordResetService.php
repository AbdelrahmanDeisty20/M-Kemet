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
            return $this->notFoundResponse(__('messages.user_not_found'));
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

        return $this->successResponse(null, __('messages.resetOtpSentSuccessfully'));
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
            return $this->errorResponse(__('messages.otp_invalid'), 400);
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return $this->notFoundResponse(__('messages.user_not_found'));
        }

        return $this->successResponse(
            new \App\Http\Resources\UserResetPasswordResource($user, $code),
            __('messages.resetOtpValid')
        );
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
            return $this->errorResponse(__('messages.otp_invalid'), 400);
        }

        $user = User::where('email', $data['email'])->first();
        if (!$user) {
            return $this->notFoundResponse(__('messages.user_not_found'));
        }

        $user->password = Hash::make($data['password']);
        $user->save();

        // مسح الـ OTP وإبطال كل الجلسات السابقة للأمان
        Otp::where('email', $data['email'])->where('type', 'reset_password')->delete();
        $user->tokens()->delete();

        return $this->successResponse(null, __('messages.passwordResetSuccessfully'));
    }
}
