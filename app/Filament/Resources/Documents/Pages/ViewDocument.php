<?php

namespace App\Filament\Resources\Documents\Pages;

use App\Filament\Resources\Documents\DocumentResource;
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
                ->action(function () {
                    $user = $this->getRecord();
                    
                    // 1. Approve all documents
                    $user->documents()->update([
                        'is_approved'      => true,
                        'rejection_reason' => null,
                    ]);

                    // 2. Approve video if exists
                    if ($user->video) {
                        $user->video()->update([
                            'status'           => 'approved',
                            'rejection_reason' => null,
                        ]);
                    }

                    // 3. Approve UserProfile status
                    if ($user->candidateProfile) {
                        $user->candidateProfile()->update([
                            'status'           => 'approved',
                            'rejection_reason' => null,
                        ]);
                    }

                    Notification::make()
                        ->title('تم قبول واعتماد كافة المستندات وتفعيل الباحث عن العمل (UserProfile = approved) بنجاح')
                        ->success()
                        ->send();
                }),

            Action::make('reject_all')
                ->label('رفض كافة المستندات والباحث عن العمل')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->modalHeading('تحديد أسباب الرفض للمستندات والملف الشخصي')
                ->modalDescription('يرجى كتابة سبب الرفض للمستندات والملفات التي ليس لها سبب رفض مسجل بعد')
                ->form(function (): array {
                    $user = $this->getRecord();
                    $fields = [];

                    $docTypes = [
                        'cv'             => 'السيرة الذاتية (CV)',
                        'national_id'    => 'الهوية الوطنية',
                        'passport'       => 'جواز السفر',
                        'personal_photo' => 'صورة شخصية',
                    ];

                    // 1. سبب رفض ملف الباحث عن العمل (إذا لم يكن له سبب مسجل بعد)
                    if ($user->candidateProfile && empty($user->candidateProfile->rejection_reason)) {
                        $fields[] = Textarea::make('candidate_profile_reason')
                            ->label('سبب رفض ملف الباحث عن العمل')
                            ->placeholder('أدخل سبب رفض الملف الشخصي...')
                            ->rows(2)
                            ->required();
                    }

                    // 2. سبب رفض الفيديو التعريفي (إذا لم يكن له سبب مسجل بعد)
                    if ($user->video && empty($user->video->rejection_reason)) {
                        $fields[] = Textarea::make('video_reason')
                            ->label('سبب رفض الفيديو التعريفي 🎥')
                            ->placeholder('أدخل سبب رفض الفيديو التعريفي...')
                            ->rows(2)
                            ->required();
                    }

                    // 3. أسباب رفض المستندات التي ليس لها سبب مسجل بعد
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

                    // إذا كانت كافة المستندات تمتلك أسباب رفض مسجلة من قبل
                    if (empty($fields)) {
                        $fields[] = Textarea::make('already_have_reasons_notice')
                            ->label('ملاحظة')
                            ->default('جميع المستندات وملف الباحث عن العمل تمتلك أسباب رفض مسجلة بالفعل. سيتم اعتماد رفض الجميع مع الاحتفاظ بأسباب الرفض السابقة.')
                            ->disabled()
                            ->rows(2);
                    }

                    return $fields;
                })
                ->action(function (array $data) {
                    $user = $this->getRecord();

                    // 1. تحديث كافة المستندات: المستند بدون سبب ينال السبب الجديد، والمستند صاحب السبب المحفوظ يظل كما هو
                    foreach ($user->documents as $doc) {
                        $reasonKey = "doc_reason_{$doc->id}";
                        $reason = !empty($data[$reasonKey]) 
                            ? $data[$reasonKey] 
                            : (!empty($doc->rejection_reason) ? $doc->rejection_reason : 'تم رفض المستند');

                        $doc->update([
                            'is_approved'      => false,
                            'rejection_reason' => $reason,
                        ]);
                    }

                    // 2. تحديث حالة الفيديو إن وجد
                    if ($user->video) {
                        $videoReason = !empty($data['video_reason']) 
                            ? $data['video_reason'] 
                            : (!empty($user->video->rejection_reason) ? $user->video->rejection_reason : 'تم رفض الفيديو');

                        $user->video()->update([
                            'status'           => 'rejected',
                            'rejection_reason' => $videoReason,
                        ]);
                    }

                    // 3. تحديث حالة ملف الباحث عن العمل
                    if ($user->candidateProfile) {
                        $profileReason = !empty($data['candidate_profile_reason']) 
                            ? $data['candidate_profile_reason'] 
                            : (!empty($user->candidateProfile->rejection_reason) ? $user->candidateProfile->rejection_reason : 'تم رفض الملف الشخصي');

                        $user->candidateProfile()->update([
                            'status'           => 'rejected',
                            'rejection_reason' => $profileReason,
                        ]);
                    }

                    Notification::make()
                        ->title('تم رفض المستندات وتطبيق أسباب الرفض بنجاح')
                        ->danger()
                        ->send();
                }),

            EditAction::make()
                ->label('تعديل التفاصيل'),
        ];
    }
}
