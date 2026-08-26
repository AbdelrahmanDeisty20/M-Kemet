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
