<?php

namespace App\Filament\Resources\Documents\Tables;

use App\Filament\Resources\Documents\DocumentResource;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class DocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('المستخدم / المرشح')
                    ->default(fn ($record) => $record->phone ?? $record->email ?? '-')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('approval_summary')
                    ->label('حالة الاعتماد العام')
                    ->badge()
                    ->state(function ($record): string {
                        $profileStatus = $record->candidateProfile?->status;
                        if ($profileStatus === 'approved') {
                            return 'معتمدة بالكامل (مفعل)';
                        }
                        if ($profileStatus === 'rejected') {
                            return 'مرفوض';
                        }
                        
                        $total = $record->documents->count();
                        $hasVideo = $record->video !== null;
                        if ($total === 0 && !$hasVideo) return 'لا يوجد مستندات';
                        
                        $approvedDocs = $record->documents->where('is_approved', true)->count();
                        $videoApproved = $record->video?->status === 'approved';
                        
                        if ($approvedDocs === $total && (!$hasVideo || $videoApproved)) {
                            return 'معتمدة بالكامل';
                        }
                        if ($approvedDocs === 0 && (!$hasVideo || $record->video?->status === 'rejected')) {
                            return 'غير معتمدة';
                        }
                        return 'مراجعة جزئية (' . $approvedDocs . '/' . $total . ')';
                    })
                    ->color(function (string $state): string {
                        if (str_contains($state, 'معتمدة بالكامل') || str_contains($state, 'مفعل')) return 'success';
                        if (str_contains($state, 'مرفوض') || str_contains($state, 'غير معتمدة')) return 'danger';
                        return 'warning';
                    }),
                TextColumn::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
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
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('حذف الوثائق للمحددين')
                        ->modalHeading('حذف وثائق المستندات للمستخدمين المحددين')
                        ->modalDescription('هل أنت متأكد من حذف كافة المستندات والوثائق للحسابات المحددة؟ لن يتم حذف حسابات المستخدمين.')
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                DocumentResource::deleteUserDocuments($record);
                            }

                            Notification::make()
                                ->title('تم حذف الوثائق بنجاح')
                                ->body('تم حذف كافة المستندات والوثائق للمستخدمين المحددين بنجاح بدون حذف الحسابات.')
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }
}
