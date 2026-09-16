<?php

namespace App\Filament\Resources\AppNotifications\Pages;

use App\Filament\Resources\AppNotifications\AppNotificationResource;
use App\Models\User;
use App\Models\UserFcmToken;
use App\Services\FirebaseNotificationService;
use App\Services\NotificationService;
use Filament\Resources\Pages\CreateRecord;

class CreateAppNotification extends CreateRecord
{
    protected static string $resource = AppNotificationResource::class;

    protected function afterCreate(): void
    {
        $notification = $this->record;

        try {
            $notificationService = app(NotificationService::class);
            $data = [
                'type'            => $notification->type ?? 'general',
                'notification_id' => (string) $notification->id,
            ];

            if ($notification->user_id) {
                // Targeted notification to a specific user
                $user = User::find($notification->user_id);
                if ($user && $user->is_notify) {
                    $locale = $user->locale ?? 'ar';
                    $title  = $locale === 'en' ? $notification->title_en : $notification->title_ar;
                    $body   = $locale === 'en' ? $notification->message_en : $notification->message_ar;

                    $notificationService->sendToUser($user->id, $title, $body, $data);
                }
            } else {
                // General broadcast notification to all FCM tokens
                $tokens = UserFcmToken::with('user')->get();
                $firebaseService = app(FirebaseNotificationService::class);

                foreach ($tokens as $tokenModel) {
                    $user = $tokenModel->user;
                    if ($user && !$user->is_notify) {
                        continue;
                    }

                    $locale = ($user && $user->locale) ? $user->locale : 'ar';
                    $title  = $locale === 'en' ? $notification->title_en : $notification->title_ar;
                    $body   = $locale === 'en' ? $notification->message_en : $notification->message_ar;

                    try {
                        $firebaseService->sendToToken($tokenModel->token, $title, $body, $data);
                    } catch (\Exception $e) {
                        // Suppress individual token failure
                    }
                }
            }
        } catch (\Exception $e) {
            // Fail gracefully to preserve UI experience
        }
    }
}
