<?php

namespace App\Observers;

use App\Models\UserProfile;
use App\Services\NotificationService;

class UserProfileObserver
{
    /**
     * Handle the UserProfile "updated" event.
     */
    public function updated(UserProfile $userProfile): void
    {
        if ($userProfile->wasChanged('status') || $userProfile->wasChanged('rejection_reason')) {
            $user = $userProfile->user;
            if (!$user) {
                return;
            }

            /** @var NotificationService $notificationService */
            $notificationService = app(NotificationService::class);
            $status = $userProfile->status;
            $reason = $userProfile->rejection_reason;

            if ($status === 'approved') {
                $notificationService->sendAppNotification(
                    $user->id,
                    'تم اعتماد ملفك الشخصي',
                    'Profile Approved',
                    'تهانينا! تم اعتماد ملفك الشخصي بنجاح وإتاحته للشركات ومقدمي الخدمة.',
                    'Congratulations! Your profile has been successfully approved.',
                    'profile_status',
                    ['profile_id' => $userProfile->id, 'status' => 'approved']
                );
            } elseif ($status === 'rejected') {
                $msgAr = 'عذراً، تم رفض ملفك الشخصي.';
                if (!empty($reason)) {
                    $msgAr .= " السبب: {$reason}";
                }
                $msgEn = 'Sorry, your profile has been rejected.';
                if (!empty($reason)) {
                    $msgEn .= " Reason: {$reason}";
                }

                $notificationService->sendAppNotification(
                    $user->id,
                    'تم رفض ملفك الشخصي',
                    'Profile Rejected',
                    $msgAr,
                    $msgEn,
                    'profile_status',
                    ['profile_id' => $userProfile->id, 'status' => 'rejected', 'reason' => $reason]
                );
            }
        }
    }
}
