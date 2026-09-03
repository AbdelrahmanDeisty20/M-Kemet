<?php

namespace App\Filament\Resources\Documents\Pages;

use App\Filament\Resources\Documents\DocumentResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewDocument extends ViewRecord
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve_all')
                ->label('قبول واعتماد كافة المستندات')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->action(function () {
                    $user = $this->getRecord();
                    $user->documents()->update([
                        'is_approved'      => true,
                        'rejection_reason' => null,
                    ]);

                    if ($user->video) {
                        $user->video()->update([
                            'status'           => 'approved',
                            'rejection_reason' => null,
                        ]);
                    }

                    Notification::make()
                        ->title('تم اعتماد كافة المستندات والفيديو بنجاح')
                        ->success()
                        ->send();
                }),

            Action::make('reject_video')
                ->label('رفض الفيديو التعريفي')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $this->getRecord()?->video !== null)
                ->form([
                    TextInput::make('rejection_reason')
                        ->label('سبب رفض الفيديو')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $user = $this->getRecord();
                    if ($user->video) {
                        $user->video()->update([
                            'status'           => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                        ]);

                        Notification::make()
                            ->title('تم تسجيل رفض الفيديو التعريفي')
                            ->warning()
                            ->send();
                    }
                }),

            EditAction::make()
                ->label('تعديل التفاصيل'),
        ];
    }
}
