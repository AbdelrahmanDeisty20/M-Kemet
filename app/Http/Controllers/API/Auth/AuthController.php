<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\LoginRequest;
use App\Http\Requests\API\Auth\RefreshTokenRequest;
use App\Http\Requests\API\Auth\RegisterCandidateRequest;
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

    public function registerCandidate(RegisterCandidateRequest $request): JsonResponse
    {
        return $this->authService->registerCandidate($request->validated());
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

    public function deleteAccount(): JsonResponse
    {
        return $this->authService->deleteAccount();
    }
}
