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
                TextColumn::make('documents_count')
                    ->label('عدد المستندات')
                    ->badge()
                    ->color('info')
                    ->suffix(' مستندات')
                    ->sortable(),
                TextColumn::make('documents.document_type')
                    ->label('المستندات المرفوعة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cv'             => 'success',
                        'national_id'    => 'info',
                        'passport'       => 'warning',
                        'personal_photo' => 'purple',
                        default          => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cv'             => 'السيرة الذاتية',
                        'national_id'    => 'الهوية الوطنية',
                        'passport'       => 'جواز السفر',
                        'personal_photo' => 'صورة شخصية',
                        default          => $state,
                    }),
                TextColumn::make('video.status')
                    ->label('الفيديو التعريفي')
                    ->badge()
                    ->state(fn ($record): string => match ($record->video?->status) {
                        'approved' => 'معتمد 🎥',
                        'pending'  => 'قيد المراجعة 🎥',
                        'rejected' => 'مرفوض 🎥',
                        default    => 'غير مرفوع 🚫',
                    })
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, 'معتمد')     => 'success',
                        str_contains($state, 'مراجعة')    => 'warning',
                        str_contains($state, 'مرفوض')     => 'danger',
                        default                           => 'gray',
                    }),
                TextColumn::make('approval_summary')
                    ->label('حالة الاعتماد العام')
                    ->badge()
                    ->state(function ($record): string {
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
                        if (str_contains($state, 'معتمدة بالكامل')) return 'success';
                        if (str_contains($state, 'غير معتمدة')) return 'danger';
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
