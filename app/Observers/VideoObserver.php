<?php

namespace App\Observers;

use App\Models\Video;
use App\Services\NotificationService;

class VideoObserver
{
    /**
     * Handle the Video "updated" event.
     */
    public function updated(Video $video): void
    {
        if ($video->wasChanged('status') || $video->wasChanged('rejection_reason')) {
            $user = $video->user;
            if (!$user) {
                return;
            }

            /** @var NotificationService $notificationService */
            $notificationService = app(NotificationService::class);
            $status = $video->status;
            $reason = $video->rejection_reason;

            if ($status === 'approved') {
                $notificationService->sendAppNotification(
                    $user->id,
                    'تمت الموافقة على الفيديو التعريفي',
                    'Video Approved',
                    'تمت الموافقة على الفيديو التعريفي الخاص بك بنجاح.',
                    'Your introductory video has been approved.',
                    'video_status',
                    ['video_id' => $video->id, 'status' => 'approved']
                );
            } elseif ($status === 'rejected') {
                $msgAr = 'عذراً، تم رفض الفيديو التعريفي الخاص بك.';
                if (!empty($reason)) {
                    $msgAr .= " السبب: {$reason}";
                }
                $msgEn = 'Sorry, your introductory video has been rejected.';
                if (!empty($reason)) {
                    $msgEn .= " Reason: {$reason}";
                }

                $notificationService->sendAppNotification(
                    $user->id,
                    'تم رفض الفيديو التعريفي',
                    'Video Rejected',
                    $msgAr,
                    $msgEn,
                    'video_status',
                    ['video_id' => $video->id, 'status' => 'rejected', 'reason' => $reason]
                );
            }
        }
    }
}
