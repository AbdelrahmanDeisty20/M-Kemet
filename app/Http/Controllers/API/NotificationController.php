<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\StoreFcmTokenRequest;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function NotificationStatus(): JsonResponse
    {
        return $this->notificationService->NotificationStatus();
    }

    public function TurnOnNotification(): JsonResponse
    {
        return $this->notificationService->TurnOnNotification();
    }

    public function TurnOffNotification(): JsonResponse
    {
        return $this->notificationService->TurnOffNotification();
    }

    public function sendToken(StoreFcmTokenRequest $request): JsonResponse
    {
        return $this->notificationService->sendToken($request->validated());
    }

    

    public function notifications(): JsonResponse
    {
        return $this->notificationService->notifications();
    }

    public function readNotification(string $id): JsonResponse
    {
        return $this->notificationService->readNotification($id);
    }

    public function readAllNotifications(): JsonResponse
    {
        return $this->notificationService->readAllNotifications();
    }

    public function deleteNotification(string $id): JsonResponse
    {
        return $this->notificationService->deleteNotification($id);
    }

    public function deleteAllNotifications(): JsonResponse
    {
        return $this->notificationService->deleteAllNotifications();
    }
}
