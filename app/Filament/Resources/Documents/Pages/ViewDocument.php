<?php

namespace App\Filament\Resources\Documents\Pages;

use App\Filament\Resources\Documents\DocumentResource;
use App\Models\Document;
use App\Models\UserProfile;
use App\Models\Video;
use App\Services\NotificationService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewDocument extends ViewRecord
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve_all')
                ->label('قبول واعتماد كافة المستندات والباحث عن العمل')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('تأكيد قبول كافة المستندات والملف الشخصي')
                ->modalDescription('هل أنت متأكد من قبول واعتـماد جميع المستندات والفيديو والملف الشخصي لهذا المرشح؟')
                ->action(function () {
                    $user = $this->getRecord();

                    // 1. Update documents without triggering individual single observers
                    Document::withoutEvents(function () use ($user) {
                        foreach ($user->documents as $doc) {
                            $doc->update([
                                'is_approved'      => true,
                                'rejection_reason' => null,
                            ]);
                        }
                    });

                    // 2. Update video if present
                    if ($user->video) {
                        Video::withoutEvents(function () use ($user) {
                            $user->video->update([
                                'status'           => 'approved',
                                'rejection_reason' => null,
                            ]);
                        });
                    }

                    // 3. Update candidate profile status
                    if ($user->candidateProfile) {
                        UserProfile::withoutEvents(function () use ($user) {
                            $user->candidateProfile->update([
                                'status'           => 'approved',
                                'rejection_reason' => null,
                            ]);
                        });
                    }

                    // 4. Send ONE single unified notification for bulk approval
                    /** @var NotificationService $notificationService */
                    $notificationService = app(NotificationService::class);
                    $notificationService->sendAppNotification(
                        $user->id,
                        'تم اعتماد كافة المستندات والملف الشخصي',
                        'All Documents & Profile Approved',
                        'تهانينا! تم مراجعة واعتـماد كافة مستنداتك وملفك الشخصي بنجاح، وحسابك الآن مفعل بالكامل.',
                        'Congratulations! All your documents and profile have been successfully reviewed and approved.',
                        'documents_bulk_approval',
                        ['user_id' => $user->id, 'status' => 'approved']
                    );

                    Notification::make()
                        ->title('تم قبول واعتماد كافة المستندات وتفعيل الباحث عن العمل بنجاح')
                        ->success()
                        ->send();

                    // Redirect to view route to force immediate UI refresh on first click
                    return redirect(DocumentResource::getUrl('view', ['record' => $user->id]));
                }),

            Action::make('reject_all')
                ->label('رفض كافة المستندات والباحث عن العمل')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->modalHeading('تحديد أسباب الرفض للمستندات والملف الشخصي')
                ->modalDescription('أسباب الرفض السابقة مسجلة ومملوءة تلقائياً. يمكنك مراجعتها أو تعديلها أو إضافة أسباب جديدة.')
                ->form(function (): array {
                    $user = $this->getRecord();
                    $fields = [];

                    $docTypes = [
                        'cv'             => 'السيرة الذاتية (CV)',
                        'national_id'    => 'الهوية الوطنية',
                        'passport'       => 'جواز السفر',
                        'personal_photo' => 'صورة شخصية',
                    ];

                    // 1. سبب رفض ملف الباحث عن العمل (مسبق الملء بالسبب السابق إن وجد)
                    if ($user->candidateProfile) {
                        $fields[] = Textarea::make('candidate_profile_reason')
                            ->label('سبب رفض ملف الباحث عن العمل')
                            ->placeholder('أدخل سبب رفض الملف الشخصي (اتركه فارغاً إن كان مقبولاً)...')
                            ->default($user->candidateProfile->rejection_reason)
                            ->rows(2)
                            ->nullable();
                    }

                    // 2. سبب رفض الفيديو التعريفي (مسبق الملء بالسبب السابق إن وجد)
                    if ($user->video) {
                        $fields[] = Textarea::make('video_reason')
                            ->label('سبب رفض الفيديو التعريفي 🎥')
                            ->placeholder('أدخل سبب رفض الفيديو التعريفي (اتركه فارغاً إن كان مقبولاً)...')
                            ->default($user->video->rejection_reason)
                            ->rows(2)
                            ->nullable();
                    }

                    // 3. أسباب رفض كافة المستندات المرفوعة (مسبقة الملء بأسبابها السابقة)
                    foreach ($user->documents as $doc) {
                        $typeLabel = $docTypes[$doc->document_type] ?? $doc->document_type;
                        $fields[] = Textarea::make("doc_reason_{$doc->id}")
                            ->label("سبب رفض مستند: {$typeLabel}")
                            ->placeholder("أدخل سبب رفض {$typeLabel} (اتركه فارغاً إن كان مقبولاً)...")
                            ->default($doc->rejection_reason)
                            ->rows(2)
                            ->nullable();
                    }

                    return $fields;
                })
                ->action(function (array $data) {
                    $user = $this->getRecord();
                    $docTypes = [
                        'cv'             => 'السيرة الذاتية (CV)',
                        'national_id'    => 'الهوية الوطنية',
                        'passport'       => 'جواز السفر',
                        'personal_photo' => 'صورة شخصية',
                    ];

                    $rejectionsAr = [];
                    $rejectionsEn = [];

                    // 1. تحديث المستندات دون إطلاق إشعارات مفردة
                    Document::withoutEvents(function () use ($user, $data, $docTypes, &$rejectionsAr, &$rejectionsEn) {
                        foreach ($user->documents as $doc) {
                            $reasonKey = "doc_reason_{$doc->id}";
                            $reason = !empty($data[$reasonKey]) ? trim($data[$reasonKey]) : null;
                            $typeLabel = $docTypes[$doc->document_type] ?? $doc->document_type;

                            if (!empty($reason)) {
                                $doc->update([
                                    'is_approved'      => false,
                                    'rejection_reason' => $reason,
                                ]);
                                $rejectionsAr[] = "📌 مستند ({$typeLabel}):\n-السبب: {$reason}";
                                $rejectionsEn[] = "📌 Document ({$typeLabel}):\n-Reason: {$reason}";
                            } else {
                                $doc->update([
                                    'is_approved'      => true,
                                    'rejection_reason' => null,
                                ]);
                            }
                        }
                    });

                    // 2. تحديث حالة الفيديو التعريفي
                    if ($user->video) {
                        Video::withoutEvents(function () use ($user, $data, &$rejectionsAr, &$rejectionsEn) {
                            $videoReason = !empty($data['video_reason']) ? trim($data['video_reason']) : null;
                            if (!empty($videoReason)) {
                                $user->video->update([
                                    'status'           => 'rejected',
                                    'rejection_reason' => $videoReason,
                                ]);
                                $rejectionsAr[] = "📌 الفيديو التعريفي:\n-السبب: {$videoReason}";
                                $rejectionsEn[] = "📌 Intro Video:\n-Reason: {$videoReason}";
                            } else {
                                $user->video->update([
                                    'status'           => 'approved',
                                    'rejection_reason' => null,
                                ]);
                            }
                        });
                    }

                    // 3. تحديث حالة ملف الباحث عن العمل
                    if ($user->candidateProfile) {
                        UserProfile::withoutEvents(function () use ($user, $data, &$rejectionsAr, &$rejectionsEn) {
                            $profileReason = !empty($data['candidate_profile_reason']) ? trim($data['candidate_profile_reason']) : null;
                            if (!empty($profileReason)) {
                                $user->candidateProfile->update([
                                    'status'           => 'rejected',
                                    'rejection_reason' => $profileReason,
                                ]);
                                $rejectionsAr[] = "📌 الملف الشخصي:\n-السبب: {$profileReason}";
                                $rejectionsEn[] = "📌 Candidate Profile:\n-Reason: {$profileReason}";
                            } else {
                                $user->candidateProfile->update([
                                    'status'           => 'approved',
                                    'rejection_reason' => null,
                                ]);
                            }
                        });
                    }

                    // 4. إرسال إشعار موحد واحد شامل لكافة تفاصيل وأسباب الرفض
                    /** @var NotificationService $notificationService */
                    $notificationService = app(NotificationService::class);

                    if (!empty($rejectionsAr)) {
                        $fullMsgAr = "تم مراجعة مستنداتك وحسابك وتحديث حالتها كالتالي:\n\n" . implode("\n\n", $rejectionsAr);
                        $fullMsgEn = "Your documents and profile have been reviewed with the following updates:\n\n" . implode("\n\n", $rejectionsEn);

                        $notificationService->sendAppNotification(
                            $user->id,
                            'تحديث حالة المستندات والملف الشخصي',
                            'Documents & Profile Status Update',
                            $fullMsgAr,
                            $fullMsgEn,
                            'documents_bulk_rejection',
                            ['user_id' => $user->id, 'status' => 'rejected']
                        );
                    } else {
                        // إذا تم مسح كافة الأسباب تصبح جميع الحالات مقبولة بالإجماع
                        $notificationService->sendAppNotification(
                            $user->id,
                            'تم اعتماد كافة المستندات والملف الشخصي',
                            'All Documents & Profile Approved',
                            'تهانينا! تم مراجعة واعتـماد كافة مستنداتك وملفك الشخصي بنجاح.',
                            'Congratulations! All your documents and profile have been approved.',
                            'documents_bulk_approval',
                            ['user_id' => $user->id, 'status' => 'approved']
                        );
                    }

                    Notification::make()
                        ->title('تم تطبيق أسباب الرفض وإرسال الإشعار الموحد للمرشح بنجاح')
                        ->danger()
                        ->send();

                    // Redirect to view route to force immediate UI refresh on first click
                    return redirect(DocumentResource::getUrl('view', ['record' => $user->id]));
                }),

            EditAction::make()
                ->label('تعديل التفاصيل'),
        ];
    }
}
