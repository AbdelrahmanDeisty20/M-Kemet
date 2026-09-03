<?php

namespace App\Filament\Resources\Documents\Pages;

use App\Filament\Resources\Documents\DocumentResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
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

            EditAction::make()
                ->label('تعديل التفاصيل'),
        ];
    }
}
