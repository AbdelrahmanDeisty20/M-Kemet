<?php

namespace App\Filament\Resources\Applications\Pages;

use App\Filament\Resources\Applications\ApplicationResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewApplication extends ViewRecord
{
    protected static string $resource = ApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('change_status')
                ->label('تعديل حالة الطلب')
                ->icon('heroicon-o-pencil-square')
                ->color('primary')
                ->form([
                    Select::make('status')
                        ->label('حالة الطلب الجديدة')
                        ->options([
                            'pending'   => 'قيد الانتظار',
                            'accepted'  => 'مقبول / تم التوافق',
                            'rejected'  => 'مرفوض',
                            'completed' => 'مكتمل',
                        ])
                        ->default(fn ($record) => $record->status)
                        ->required(),
                    Textarea::make('notes')
                        ->label('الملاحظات')
                        ->default(fn ($record) => $record->notes),
                ])
                ->action(function (array $data) {
                    $application = $this->getRecord();
                    $application->update([
                        'status' => $data['status'],
                        'notes'  => $data['notes'],
                    ]);

                    Notification::make()
                        ->title('تم تحديث حالة طلب التواصل بنجاح')
                        ->success()
                        ->send();
                }),

            EditAction::make()
                ->label('تعديل التفاصيل'),
        ];
    }
}
