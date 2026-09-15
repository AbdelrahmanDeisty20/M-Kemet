<?php

namespace App\Services;

use App\Http\Resources\NotificationResource;
use App\Http\Resources\NotifyStatusResource;
use App\Models\AppNotification;
use App\Models\User;
use App\Models\UserFcmToken;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class NotificationService
{
    use ApiResponse;

    protected FirebaseNotificationService $firebaseService;

    public function __construct(FirebaseNotificationService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function NotificationStatus(): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return $this->errorResponse(__('messages.user_not_found'), 401);
        }

        return $this->successResponse(
            new NotifyStatusResource($user),
            __('messages.notification_status')
        );
    }

    public function TurnOnNotification(): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return $this->errorResponse(__('messages.user_not_found'), 401);
        }

        $user->update(['is_notify' => true]);

        return $this->successResponse(
            new NotifyStatusResource($user),
            __('messages.notification_turned_on')
        );
    }

    public function TurnOffNotification(): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return $this->errorResponse(__('messages.user_not_found'), 401);
        }

        $user->update(['is_notify' => false]);

        return $this->successResponse(
            new NotifyStatusResource($user),
            __('messages.notification_turned_off')
        );
    }

    public function sendToken(array $data): JsonResponse
    {
        $userId = $data['user_id'] ?? auth()->id();

        if (!empty($data['device_id'])) {
            $token = UserFcmToken::updateOrCreate(
                ['device_id' => $data['device_id']],
                [
                    'user_id' => $userId,
                    'token'   => $data['token'],
                ]
            );
        } else {
            $token = UserFcmToken::updateOrCreate(
                ['token' => $data['token']],
                [
                    'device_id' => null,
                    'user_id'   => $userId,
                ]
            );
        }

        return $this->successResponse(
            $token,
            __('messages.fcm_token_stored_successfully')
        );
    }

    public function sendToUser(int $userId, string $title, string $body, array $data = []): array
    {
        $user = User::find($userId);
        if ($user && !$user->is_notify) {
            return [
                'status'  => false,
                'message' => 'User has disabled notifications',
                'count'   => 0,
                'details' => [],
            ];
        }

        $tokens = UserFcmToken::where('user_id', $userId)->pluck('token')->toArray();

        $results = [];
        foreach ($tokens as $token) {
            try {
                $results[] = $this->firebaseService->sendToToken($token, $title, $body, $data);
            } catch (\Exception $e) {
                // Ignore single token send errors
            }
        }

        return [
            'status'  => true,
            'message' => 'Notification sent to specific user',
            'count'   => count($tokens),
            'details' => $results,
        ];
    }

    /**
     * Send an AppNotification record & Push Notification to a specific user.
     */
    public function sendAppNotification(
        int $userId,
        string $titleAr,
        string $titleEn,
        string $messageAr,
        string $messageEn,
        string $type = 'general',
        array $data = []
    ): AppNotification {
        $notification = AppNotification::create([
            'user_id'    => $userId,
            'title_ar'   => $titleAr,
            'title_en'   => $titleEn,
            'message_ar' => $messageAr,
            'message_en' => $messageEn,
            'type'       => $type,
            'data'       => $data,
            'is_read'    => false,
        ]);

        $title = app()->getLocale() === 'en' ? $titleEn : $titleAr;
        $body  = app()->getLocale() === 'en' ? $messageEn : $messageAr;

        $this->sendToUser($userId, $title, $body, $data);

        return $notification;
    }

    public function notifications(): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return $this->errorResponse(__('messages.user_not_found'), 401);
        }

        $notifications = AppNotification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return $this->paginatedResponse(
            NotificationResource::class,
            $notifications,
            __('messages.notifications_retrieved_successfully')
        );
    }

    public function readNotification(string $id): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return $this->errorResponse(__('messages.user_not_found'), 401);
        }

        $notification = AppNotification::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return $this->notFoundResponse(__('messages.notification_not_found'));
        }

        $notification->update(['is_read' => true]);

        return $this->successResponse(
            new NotificationResource($notification),
            __('messages.notification_read_successfully')
        );
    }

    public function readAllNotifications(): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return $this->errorResponse(__('messages.user_not_found'), 401);
        }

        AppNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return $this->successResponse(
            [],
            __('messages.notifications_read_successfully')
        );
    }

    public function deleteNotification(string $id): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return $this->errorResponse(__('messages.user_not_found'), 401);
        }

        $notification = AppNotification::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return $this->notFoundResponse(__('messages.notification_not_found'));
        }

        $notification->delete();

        return $this->successResponse(
            [],
            __('messages.notification_deleted_successfully')
        );
    }

    public function deleteAllNotifications(): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return $this->errorResponse(__('messages.user_not_found'), 401);
        }

        AppNotification::where('user_id', $user->id)->delete();

        return $this->successResponse(
            [],
            __('messages.notifications_deleted_successfully')
        );
    }
}
