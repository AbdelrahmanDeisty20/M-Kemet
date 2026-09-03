<?php

namespace App\Filament\Resources\Companies\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CompaniesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('المستخدم المرتبط')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('company_name')
                    ->label('اسم الشركة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('commercial_register_number')
                    ->label('رقم السجل التجاري')
                    ->searchable(),
                TextColumn::make('industry')
                    ->label('قطاع النشاط')
                    ->searchable()
                    ->badge()
                    ->color('warning'),
                TextColumn::make('country.name_ar')
                    ->label('الدولة')
                    ->searchable(),
                TextColumn::make('city')
                    ->label('المدينة')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('حالة الشركة')
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
                        'approved' => 'معتمدة',
                        'pending'  => 'قيد المراجعة',
                        'rejected' => 'مرفوضة',
                    ]),
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
