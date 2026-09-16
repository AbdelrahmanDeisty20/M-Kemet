<?php

namespace App\Observers;

use App\Models\Application;
use App\Models\ApplicationStatusHistory;
use App\Services\NotificationService;

class ApplicationObserver
{
    /**
     * Handle the Application "created" event.
     */
    public function created(Application $application): void
    {
        $application->loadMissing(['company.user', 'candidateProfile.user']);

        $companyName   = $application->company?->company_name ?? 'شركة';
        $candidateUser = $application->candidateProfile?->user;

        if ($candidateUser) {
            /** @var NotificationService $notificationService */
            $notificationService = app(NotificationService::class);
            $notificationService->sendAppNotification(
                $candidateUser->id,
                'طلب تواصل جديد 📩',
                'New Contact Request 📩',
                "قامت شركة ({$companyName}) بإرسال طلب تواصل جديد معك (كود الطلب: {$application->code}).",
                "Company ({$companyName}) sent a new contact request to you (Code: {$application->code}).",
                'contact_request',
                [
                    'application_id' => $application->id,
                    'code'           => $application->code,
                    'status'         => $application->status,
                ]
            );
        }
    }

    /**
     * Handle the Application "updated" event.
     */
    public function updated(Application $application): void
    {
        if (!$application->wasChanged('status')) {
            return;
        }

        $application->loadMissing(['company.user', 'candidateProfile.user']);

        $companyName   = $application->company?->company_name ?? 'الشركة';
        $companyUser   = $application->company?->user;
        $candidateUser = $application->candidateProfile?->user;

        $candidateName = $candidateUser?->name ?? 'الباحث عن عمل';
        $code          = $application->code;
        $status        = $application->status;

        /** @var NotificationService $notificationService */
        $notificationService = app(NotificationService::class);

        // Record status change in ApplicationStatusHistory
        try {
            ApplicationStatusHistory::create([
                'application_id'     => $application->id,
                'status'             => $status,
                'changed_by_user_id' => auth()->id() ?? $companyUser?->id,
                'notes'              => $application->notes ?? "تحديث حالة الطلب إلى {$status}",
            ]);
        } catch (\Throwable $e) {
            // Ignore history record creation errors if any
        }

        $statusLabels = [
            'accepted'  => ['ar' => 'مقبول / تم التوافق', 'en' => 'Accepted'],
            'rejected'  => ['ar' => 'مرفوض',             'en' => 'Rejected'],
            'completed' => ['ar' => 'مكتمل',             'en' => 'Completed'],
            'pending'   => ['ar' => 'قيد الانتظار',        'en' => 'Pending'],
        ];

        $labelAr = $statusLabels[$status]['ar'] ?? $status;
        $labelEn = $statusLabels[$status]['en'] ?? $status;

        // 1. Send Notification to Candidate
        if ($candidateUser) {
            $titleAr = match ($status) {
                'accepted'  => 'تم قبول طلب التواصل 🤝',
                'completed' => 'تم اكتمال طلب التواصل ✅',
                'rejected'  => 'رفض طلب التواصل ❌',
                default     => 'تحديث في حالة طلب التواصل ℹ️',
            };

            $titleEn = match ($status) {
                'accepted'  => 'Contact Request Accepted 🤝',
                'completed' => 'Contact Request Completed ✅',
                'rejected'  => 'Contact Request Rejected ❌',
                default     => 'Contact Request Status Update ℹ️',
            };

            $msgAr = "تغيرت حالة طلب التواصل (كود: {$code}) مع شركة ({$companyName}) إلى: {$labelAr}.";
            $msgEn = "Status of contact request (Code: {$code}) with company ({$companyName}) has been updated to: {$labelEn}.";

            $notificationService->sendAppNotification(
                $candidateUser->id,
                $titleAr,
                $titleEn,
                $msgAr,
                $msgEn,
                'contact_request_update',
                [
                    'application_id' => $application->id,
                    'code'           => $code,
                    'status'         => $status,
                ]
            );
        }

        // 2. Send Notification to Company User
        if ($companyUser) {
            $titleAr = match ($status) {
                'accepted'  => 'تم قبول طلب التواصل 🤝',
                'completed' => 'تم اكتمال طلب التواصل ✅',
                'rejected'  => 'رفض طلب التواصل ❌',
                default     => 'تحديث في حالة طلب التواصل ℹ️',
            };

            $titleEn = match ($status) {
                'accepted'  => 'Contact Request Accepted 🤝',
                'completed' => 'Contact Request Completed ✅',
                'rejected'  => 'Contact Request Rejected ❌',
                default     => 'Contact Request Status Update ℹ️',
            };

            $msgAr = "تغيرت حالة طلب التواصل الخاص بكم (كود: {$code}) للباحث ({$candidateName}) إلى: {$labelAr}.";
            $msgEn = "Status of your contact request (Code: {$code}) for candidate ({$candidateName}) has been updated to: {$labelEn}.";

            $notificationService->sendAppNotification(
                $companyUser->id,
                $titleAr,
                $titleEn,
                $msgAr,
                $msgEn,
                'contact_request_update',
                [
                    'application_id' => $application->id,
                    'code'           => $code,
                    'status'         => $status,
                ]
            );
        }
    }
}
