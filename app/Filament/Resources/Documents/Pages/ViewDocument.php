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
                ->modalHeading('رفض المستندات وتحديد سبب الرفض لكل عنصر')
                ->modalDescription('يرجى كتابة سبب الرفض المخصص لكل مستند على حدة والملف الشخصي')
                ->form(function (): array {
                    $user = $this->getRecord();
                    $fields = [];

                    // 1. سبب رفض ملف الباحث عن العمل
                    $fields[] = Textarea::make('candidate_profile_reason')
                        ->label('سبب رفض ملف الباحث عن العمل')
                        ->default(fn () => $user->candidateProfile?->rejection_reason)
                        ->placeholder('أدخل سبب رفض الملف الشخصي...')
                        ->rows(2)
                        ->required();

                    // 2. سبب رفض الفيديو التعريفي (إن وجد)
                    if ($user->video) {
                        $fields[] = Textarea::make('video_reason')
                            ->label('سبب رفض الفيديو التعريفي 🎥')
                            ->default(fn () => $user->video?->rejection_reason)
                            ->placeholder('أدخل سبب رفض الفيديو التعريفي...')
                            ->rows(2)
                            ->required();
                    }

                    // 3. أسباب رفض كل مستند من المستندات المرفوعة
                    $docTypes = [
                        'cv'             => 'السيرة الذاتية (CV)',
                        'national_id'    => 'الهوية الوطنية',
                        'passport'       => 'جواز السفر',
                        'personal_photo' => 'صورة شخصية',
                    ];

                    foreach ($user->documents as $doc) {
                        $typeLabel = $docTypes[$doc->document_type] ?? $doc->document_type;
                        $fields[] = Textarea::make("doc_reason_{$doc->id}")
                            ->label("سبب رفض مستند: {$typeLabel}")
                            ->default($doc->rejection_reason)
                            ->placeholder("أدخل سبب رفض {$typeLabel}...")
                            ->rows(2)
                            ->required();
                    }

                    return $fields;
                })
                ->action(function (array $data) {
                    $user = $this->getRecord();

                    // 1. تحديث كافة المستندات بحيث يملك كل مستند سبب الرفض الخاص به
                    foreach ($user->documents as $doc) {
                        $reasonKey = "doc_reason_{$doc->id}";
                        $reason = !empty($data[$reasonKey]) ? $data[$reasonKey] : 'تم رفض المستند';

                        $doc->update([
                            'is_approved'      => false,
                            'rejection_reason' => $reason,
                        ]);
                    }

                    // 2. تحديث حالة الفيديو إن وجد
                    if ($user->video) {
                        $user->video()->update([
                            'status'           => 'rejected',
                            'rejection_reason' => $data['video_reason'] ?? 'تم رفض الفيديو',
                        ]);
                    }

                    // 3. تحديث حالة ملف الباحث عن العمل
                    if ($user->candidateProfile) {
                        $user->candidateProfile()->update([
                            'status'           => 'rejected',
                            'rejection_reason' => $data['candidate_profile_reason'] ?? 'تم رفض الملف الشخصي',
                        ]);
                    }

                    Notification::make()
                        ->title('تم رفض المستندات وتطبيق سبب الرفض الخاص لكل مستند بنجاح')
                        ->danger()
                        ->send();
                }),

            EditAction::make()
                ->label('تعديل التفاصيل'),
        ];
    }
}
