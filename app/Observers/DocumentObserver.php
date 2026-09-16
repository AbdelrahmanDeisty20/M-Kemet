<?php

namespace App\Observers;

use App\Models\Document;
use App\Services\NotificationService;

class DocumentObserver
{
    /**
     * Handle the Document "updated" event.
     */
    public function updated(Document $document): void
    {
        if ($document->wasChanged('is_approved') || $document->wasChanged('rejection_reason')) {
            $user = $document->user;
            if (!$user) {
                return;
            }

            /** @var NotificationService $notificationService */
            $notificationService = app(NotificationService::class);
            $docTypeName = $document->document_type_name;

            if ($document->is_approved) {
                $notificationService->sendAppNotification(
                    $user->id,
                    'تمت الموافقة على المستند',
                    'Document Approved',
                    "تمت الموافقة على مستندك ({$docTypeName}) بنجاح.",
                    "Your document ({$docTypeName}) has been approved.",
                    'document_status',
                    ['document_id' => $document->id, 'document_type' => $document->document_type, 'is_approved' => true]
                );
            } else {
                $reason = $document->rejection_reason;
                $msgAr = "تم رفض مستندك ({$docTypeName}).";
                if (!empty($reason)) {
                    $msgAr .= " السبب: {$reason}";
                }
                $msgEn = "Your document ({$docTypeName}) has been rejected.";
                if (!empty($reason)) {
                    $msgEn .= " Reason: {$reason}";
                }

                $notificationService->sendAppNotification(
                    $user->id,
                    'تم رفض المستند',
                    'Document Rejected',
                    $msgAr,
                    $msgEn,
                    'document_status',
                    ['document_id' => $document->id, 'document_type' => $document->document_type, 'is_approved' => false, 'reason' => $reason]
                );
            }
        }
    }
}
