<?php

namespace App\Filament\Resources\Documents\Pages;

use App\Filament\Resources\Documents\DocumentResource;
use App\Models\Document;
use App\Models\UserProfile;
use App\Models\Video;
use App\Services\NotificationService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
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
                ->modalDescription('يظهر أدناه فقط المستندات والملفات التي ليس لها سبب رفض مسجل بعد لتحديد أسباب الرفض لها.')
                ->form(function (): array {
                    $user = $this->getRecord();
                    $fields = [];

                    $docTypes = [
                        'cv'             => 'السيرة الذاتية (CV)',
                        'national_id'    => 'الهوية الوطنية',
                        'passport'       => 'جواز السفر',
                        'personal_photo' => 'صورة شخصية',
                    ];

                    // 1. سبب رفض ملف الباحث عن العمل (فقط إذا لم يكن له سبب مسجل من قبل)
                    if ($user->candidateProfile && empty($user->candidateProfile->rejection_reason)) {
                        $fields[] = Textarea::make('candidate_profile_reason')
                            ->label('سبب رفض ملف الباحث عن العمل')
                            ->placeholder('أدخل سبب رفض الملف الشخصي...')
                            ->rows(2)
                            ->required();
                    }

                    // 2. سبب رفض الفيديو التعريفي (فقط إذا لم يكن له سبب مسجل من قبل)
                    if ($user->video && empty($user->video->rejection_reason)) {
                        $fields[] = Textarea::make('video_reason')
                            ->label('سبب رفض الفيديو التعريفي 🎥')
                            ->placeholder('أدخل سبب رفض الفيديو التعريفي...')
                            ->rows(2)
                            ->required();
                    }

                    // 3. أسباب رفض المستندات المرفوعة (فقط للمستندات التي ليس لها سبب مسجل بعد)
                    foreach ($user->documents as $doc) {
                        if (empty($doc->rejection_reason)) {
                            $typeLabel = $docTypes[$doc->document_type] ?? $doc->document_type;
                            $fields[] = Textarea::make("doc_reason_{$doc->id}")
                                ->label("سبب رفض مستند: {$typeLabel}")
                                ->placeholder("أدخل سبب رفض {$typeLabel}...")
                                ->rows(2)
                                ->required();
                        }
                    }

                    // إذا كانت كافة المستندات وملف المرشح تمتلك أسباب رفض مسجلة بالفعل
                    if (empty($fields)) {
                        $fields[] = Textarea::make('already_have_reasons_notice')
                            ->label('ملاحظة')
                            ->default('جميع المستندات وملف الباحث عن العمل تمتلك أسباب رفض مسجلة بالفعل. سيتم تأكيد رفض الجميع وإرسال الإشعار الموحد.')
                            ->disabled()
                            ->rows(2);
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

                    // 1. تحديث المستندات: الاحتفاظ بالسبب القديم للمستندات المرفوضة سابقاً وتطبيق السبب الجديد للمستندات المرفوضة حالياً
                    Document::withoutEvents(function () use ($user, $data, $docTypes, &$rejectionsAr, &$rejectionsEn) {
                        foreach ($user->documents as $doc) {
                            $reasonKey = "doc_reason_{$doc->id}";
                            $reason = !empty($data[$reasonKey]) 
                                ? trim($data[$reasonKey]) 
                                : (!empty($doc->rejection_reason) ? $doc->rejection_reason : 'تم رفض المستند');

                            $typeLabel = $docTypes[$doc->document_type] ?? $doc->document_type;

                            $doc->update([
                                'is_approved'      => false,
                                'rejection_reason' => $reason,
                            ]);
                            $rejectionsAr[] = "📌 مستند ({$typeLabel}):\n-السبب: {$reason}";
                            $rejectionsEn[] = "📌 Document ({$typeLabel}):\n-Reason: {$reason}";
                        }
                    });

                    // 2. تحديث حالة الفيديو التعريفي
                    if ($user->video) {
                        Video::withoutEvents(function () use ($user, $data, &$rejectionsAr, &$rejectionsEn) {
                            $videoReason = !empty($data['video_reason']) 
                                ? trim($data['video_reason']) 
                                : (!empty($user->video->rejection_reason) ? $user->video->rejection_reason : 'تم رفض الفيديو');

                            $user->video->update([
                                'status'           => 'rejected',
                                'rejection_reason' => $videoReason,
                            ]);
                            $rejectionsAr[] = "📌 الفيديو التعريفي:\n-السبب: {$videoReason}";
                            $rejectionsEn[] = "📌 Intro Video:\n-Reason: {$videoReason}";
                        });
                    }

                    // 3. تحديث حالة ملف الباحث عن العمل
                    if ($user->candidateProfile) {
                        UserProfile::withoutEvents(function () use ($user, $data, &$rejectionsAr, &$rejectionsEn) {
                            $profileReason = !empty($data['candidate_profile_reason']) 
                                ? trim($data['candidate_profile_reason']) 
                                : (!empty($user->candidateProfile->rejection_reason) ? $user->candidateProfile->rejection_reason : 'تم رفض الملف الشخصي');

                            $user->candidateProfile->update([
                                'status'           => 'rejected',
                                'rejection_reason' => $profileReason,
                            ]);
                            $rejectionsAr[] = "📌 الملف الشخصي:\n-السبب: {$profileReason}";
                            $rejectionsEn[] = "📌 Candidate Profile:\n-Reason: {$profileReason}";
                        });
                    }

                    // 4. إرسال إشعار موحد واحد شامل لكافة أسباب الرفض (القديمة والجديدة)
                    /** @var NotificationService $notificationService */
                    $notificationService = app(NotificationService::class);

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

                    Notification::make()
                        ->title('تم تطبيق أسباب الرفض وإرسال الإشعار الموحد للمرشح بنجاح')
                        ->danger()
                        ->send();

                    // Redirect to view route to force immediate UI refresh on first click
                    return redirect(DocumentResource::getUrl('view', ['record' => $user->id]));
                }),

            Action::make('reject_partial')
                ->label('رفض بعض المستندات فقط')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('warning')
                ->modalHeading('رفض مستندات محددة')
                ->modalDescription('اختر المستندات والملفات التي تريد رفضها فقط — باقي المستندات ستظل على حالها. سيتم رفض حالة الباحث ليتمكن من إعادة الرفع.')
                ->form(function (): array {
                    $user = $this->getRecord();
                    $fields = [];

                    $docTypes = [
                        'cv'             => 'السيرة الذاتية (CV)',
                        'national_id'    => 'الهوية الوطنية',
                        'passport'       => 'جواز السفر',
                        'personal_photo' => 'صورة شخصية',
                    ];

                    // حقول رفض كل مستند على حدة — فقط المستندات المقبولة أو التي لا تزال قيد المراجعة
                    foreach ($user->documents as $doc) {
                        $typeLabel = $docTypes[$doc->document_type] ?? $doc->document_type;
                        $statusBadge = $doc->is_approved ? '✅ مقبول' : '⏳ قيد المراجعة';

                        $fields[] = Section::make("{$typeLabel} — {$statusBadge}")
                            ->schema([
                                Toggle::make("reject_doc_{$doc->id}")
                                    ->label("رفض هذا المستند")
                                    ->default(false)
                                    ->live(),
                                Textarea::make("doc_reason_{$doc->id}")
                                    ->label("سبب الرفض")
                                    ->placeholder("أدخل سبب رفض {$typeLabel}...")
                                    ->rows(2)
                                    ->required(fn ($get) => $get("reject_doc_{$doc->id}"))
                                    ->visible(fn ($get) => $get("reject_doc_{$doc->id}")),
                            ])
                            ->compact();
                    }

                    // الفيديو التعريفي
                    if ($user->video) {
                        $videoStatus = $user->video->status === 'approved' ? '✅ مقبول' : '⏳ قيد المراجعة';
                        $fields[] = Section::make("الفيديو التعريفي 🎥 — {$videoStatus}")
                            ->schema([
                                Toggle::make('reject_video')
                                    ->label('رفض الفيديو التعريفي')
                                    ->default(false)
                                    ->live(),
                                Textarea::make('video_reason')
                                    ->label('سبب رفض الفيديو')
                                    ->placeholder('أدخل سبب رفض الفيديو التعريفي...')
                                    ->rows(2)
                                    ->required(fn ($get) => $get('reject_video'))
                                    ->visible(fn ($get) => $get('reject_video')),
                            ])
                            ->compact();
                    }

                    // سبب رفض الملف الشخصي (إجباري دائماً لأن حالة الباحث ستتغير)
                    $fields[] = Section::make('سبب رفض الملف الشخصي (إجباري)')
                        ->description('سيتم تحديث حالة الباحث إلى "مرفوض" ليتمكن من إعادة رفع المستندات المرفوضة.')
                        ->schema([
                            Textarea::make('candidate_profile_reason')
                                ->label('سبب رفض الملف الشخصي')
                                ->placeholder('مثال: بعض مستنداتك تحتاج إعادة رفع...')
                                ->rows(2)
                                ->required(),
                        ])
                        ->compact();

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
                    $anyRejection = false;

                    // 1. رفض المستندات المحددة فقط — الباقي لا يتغير
                    Document::withoutEvents(function () use ($user, $data, $docTypes, &$rejectionsAr, &$rejectionsEn, &$anyRejection) {
                        foreach ($user->documents as $doc) {
                            if (!empty($data["reject_doc_{$doc->id}"])) {
                                $reason = trim($data["doc_reason_{$doc->id}"] ?? 'تم رفض المستند');
                                $typeLabel = $docTypes[$doc->document_type] ?? $doc->document_type;

                                $doc->update([
                                    'is_approved'      => false,
                                    'rejection_reason' => $reason,
                                ]);

                                $rejectionsAr[] = "📌 مستند ({$typeLabel}):\n-السبب: {$reason}";
                                $rejectionsEn[] = "📌 Document ({$typeLabel}):\n-Reason: {$reason}";
                                $anyRejection = true;
                            }
                        }
                    });

                    // 2. رفض الفيديو إن تم تحديده
                    if ($user->video && !empty($data['reject_video'])) {
                        Video::withoutEvents(function () use ($user, $data, &$rejectionsAr, &$rejectionsEn, &$anyRejection) {
                            $videoReason = trim($data['video_reason'] ?? 'تم رفض الفيديو');
                            $user->video->update([
                                'status'           => 'rejected',
                                'rejection_reason' => $videoReason,
                            ]);
                            $rejectionsAr[] = "📌 الفيديو التعريفي:\n-السبب: {$videoReason}";
                            $rejectionsEn[] = "📌 Intro Video:\n-Reason: {$videoReason}";
                            $anyRejection = true;
                        });
                    }

                    // 3. رفض الملف الشخصي دائماً (عشان الباحث يعرف يعيد الرفع)
                    if ($user->candidateProfile) {
                        $profileReason = trim($data['candidate_profile_reason'] ?? 'بعض مستنداتك تحتاج مراجعة');
                        UserProfile::withoutEvents(function () use ($user, $profileReason) {
                            $user->candidateProfile->update([
                                'status'           => 'rejected',
                                'rejection_reason' => $profileReason,
                            ]);
                        });
                        $rejectionsAr[] = "📌 الملف الشخصي:\n-السبب: {$profileReason}";
                        $rejectionsEn[] = "📌 Candidate Profile:\n-Reason: {$profileReason}";
                    }

                    // 4. إرسال إشعار موحد بالمستندات المرفوضة فقط
                    /** @var NotificationService $notificationService */
                    $notificationService = app(NotificationService::class);

                    $fullMsgAr = "تم مراجعة مستنداتك وتحديث بعضها كالتالي:\n\n" . implode("\n\n", $rejectionsAr);
                    $fullMsgEn = "Some of your documents have been reviewed with the following updates:\n\n" . implode("\n\n", $rejectionsEn);

                    $notificationService->sendAppNotification(
                        $user->id,
                        'تحديث حالة بعض المستندات',
                        'Partial Documents Status Update',
                        $fullMsgAr,
                        $fullMsgEn,
                        'documents_partial_rejection',
                        ['user_id' => $user->id, 'status' => 'rejected']
                    );

                    Notification::make()
                        ->title('تم رفض المستندات المحددة وإرسال الإشعار للمرشح بنجاح')
                        ->warning()
                        ->send();

                    return redirect(DocumentResource::getUrl('view', ['record' => $user->id]));
                }),

            EditAction::make()
                ->label('تعديل التفاصيل'),
        ];
    }
}
