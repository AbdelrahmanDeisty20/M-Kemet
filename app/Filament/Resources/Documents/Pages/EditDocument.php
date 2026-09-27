<?php

namespace App\Filament\Resources\Documents\Pages;

use App\Filament\Resources\Documents\DocumentResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditDocument extends EditRecord
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->label('حذف الوثائق')
                ->modalHeading('حذف وثائق المستندات')
                ->modalDescription('هل أنت متأكد من حذف جميع المستندات والوثائق المرفوعة لهذا الحساب؟ لن يتم حذف حساب المستخدم.')
                ->action(function (User $record) {
                    DocumentResource::deleteUserDocuments($record);

                    Notification::make()
                        ->title('تم حذف الوثائق بنجاح')
                        ->body('تم حذف كافة مستندات ووثائق المستخدم دون حذف الحساب.')
                        ->success()
                        ->send();

                    $this->redirect(DocumentResource::getUrl('index'));
                }),
        ];
    }
}
