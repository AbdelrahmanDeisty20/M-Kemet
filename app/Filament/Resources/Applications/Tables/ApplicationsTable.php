<?php

namespace App\Filament\Resources\Applications\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.company_name')
                    ->label('الشركة / مقدم الخدمة')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('candidateProfile.user.name')
                    ->label('الباحث عن عمل')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                TextColumn::make('status')
                    ->label('حالة طلب التواصل')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'accepted'  => 'success',
                        'pending'   => 'warning',
                        'rejected'  => 'danger',
                        'completed' => 'info',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'accepted'  => 'مقبول / تم التوافق',
                        'pending'   => 'قيد الانتظار',
                        'rejected'  => 'مرفوض',
                        'completed' => 'مكتمل',
                        default     => $state,
                    }),
                TextColumn::make('notes')
                    ->label('الملاحظات')
                    ->limit(40)
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('تاريخ الطلب')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending'   => 'قيد الانتظار',
                        'accepted'  => 'مقبول',
                        'rejected'  => 'مرفوض',
                        'completed' => 'مكتمل',
                    ]),
                SelectFilter::make('company_id')
                    ->label('الشركة')
                    ->relationship('company', 'company_name'),
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
