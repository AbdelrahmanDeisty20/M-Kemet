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
                TextColumn::make('company_name')
                    ->label('الشركة')
                    ->state(fn ($record) => $record->company_name ?? $record->user?->name ?? '-')
                    ->searchable(query: function ($query, string $search) {
                        $query->where('company_name', 'like', "%{$search}%")
                            ->orWhereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                    })
                    ->sortable(),
                TextColumn::make('user.phone')
                    ->label('رقم الهاتف')
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
