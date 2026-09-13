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
                ->form([
                    Textarea::make('rejection_reason')
                        ->label('سبب الرفض')
                        ->placeholder('أدخل سبب الرفض (اختياري)...')
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $user = $this->getRecord();
                    $reason = !empty($data['rejection_reason']) ? $data['rejection_reason'] : 'تم رفض المستندات والملف الشخصي';
                    
                    // 1. Reject all documents
                    $user->documents()->update([
                        'is_approved'      => false,
                        'rejection_reason' => $reason,
                    ]);

                    // 2. Reject video if exists
                    if ($user->video) {
                        $user->video()->update([
                            'status'           => 'rejected',
                            'rejection_reason' => $reason,
                        ]);
                    }

                    // 3. Reject UserProfile status
                    if ($user->candidateProfile) {
                        $user->candidateProfile()->update([
                            'status'           => 'rejected',
                            'rejection_reason' => $reason,
                        ]);
                    }

                    Notification::make()
                        ->title('تم رفض المستندات وتغيير حالة الباحث عن العمل إلى (rejected) بنجاح')
                        ->danger()
                        ->send();
                }),

            EditAction::make()
                ->label('تعديل التفاصيل'),
        ];
    }
}
