<?php

namespace App\Filament\Resources\Documents\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
