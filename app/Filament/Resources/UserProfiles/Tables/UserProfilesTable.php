<?php

namespace App\Filament\Resources\UserProfiles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UserProfilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('الباحث عن عمل')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('qualification.name_ar')
                    ->label('المؤهل الدراسي')
                    ->searchable()
                    ->badge()
                    ->color('info'),
                TextColumn::make('sub_specialization')
                    ->label('التخصص الفرعي')
                    ->searchable()
                    ->limit(30),
                TextColumn::make('profession.name_ar')
                    ->label('المهنة')
                    ->searchable()
                    ->badge(),
                TextColumn::make('experience_years')
                    ->label('سنوات الخبرة')
                    ->numeric()
                    ->sortable()
                    ->suffix(' سنة'),
                TextColumn::make('expected_salary')
                    ->label('الراتب المتوقع')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('willing_to_travel')
                    ->label('مستعد للسفر')
                    ->boolean(),
                TextColumn::make('status')
                    ->label('حالة الملف')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending'  => 'warning',
                        'rejected' => 'danger',
                        default    => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'approved' => 'معتمد',
                        'pending'  => 'قيد المراجعة',
                        'rejected' => 'مرفوض',
                    ]),
                SelectFilter::make('qualification_id')
                    ->label('المؤهل الدراسي')
                    ->relationship('qualification', 'name_ar'),
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
