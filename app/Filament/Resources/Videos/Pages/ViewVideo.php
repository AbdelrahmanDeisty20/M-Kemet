<?php

namespace App\Filament\Resources\Videos\Pages;

use App\Filament\Resources\Videos\VideoResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewVideo extends ViewRecord
{
    protected static string $resource = VideoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('change_status')
                ->label('تغير حالة الفيديو')
                ->icon('heroicon-o-pencil-square')
                ->color('warning')
                ->form([
                    Select::make('status')
                        ->label('الحالة الجديدة للفيديو')
                        ->options([
                            'approved' => 'معتمد',
                            'pending'  => 'قيد المراجعة',
                            'rejected' => 'مرفوض',
                        ])
                        ->default(fn ($record) => $record->status)
                        ->required()
                        ->reactive(),
                    TextInput::make('rejection_reason')
                        ->label('سبب الرفض')
                        ->visible(fn ($get) => $get('status') === 'rejected'),
                ])
                ->action(function (array $data) {
                    $video = $this->getRecord();
                    $video->update([
                        'status'           => $data['status'],
                        'rejection_reason' => $data['status'] === 'rejected' ? ($data['rejection_reason'] ?? 'تم رفض الفيديو') : null,
                    ]);

                    Notification::make()
                        ->title('تم تحديث حالة الفيديو التعريفي بنجاح')
                        ->success()
                        ->send();
                }),

            EditAction::make()
                ->label('تعديل التفاصيل'),
        ];
    }
}
