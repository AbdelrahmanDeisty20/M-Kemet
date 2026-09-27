<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('الاسم / اسم الشركة')
                    ->state(function (User $record): string {
                        if ($record->user_type === 'company') {
                            return $record->company?->company_name ?? $record->name ?? '-';
                        }
                        return $record->name ?? '-';
                    })
                    ->searchable(query: function ($query, string $search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhereHas('company', fn ($q) => $q->where('company_name', 'like', "%{$search}%"));
                    })
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('رقم الهاتف')
                    ->searchable(),
                TextColumn::make('user_type')
                    ->label('نوع المستخدم')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'admin'     => 'مدير',
                        'company'   => 'شركة',
                        'candidate' => 'باحث عن عمل',
                        default     => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'admin'     => 'danger',
                        'company'   => 'warning',
                        'candidate' => 'success',
                        default     => 'gray',
                    }),
                TextColumn::make('roles.name')
                    ->label('الأدوار')
                    ->badge()
                    ->color('info'),
                TextColumn::make('status')
                    ->label('حالة الحساب')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active'    => 'نشط',
                        'pending'   => 'قيد التفعيل',
                        'suspended' => 'موقوف',
                        default     => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'active'    => 'success',
                        'pending'   => 'warning',
                        'suspended' => 'danger',
                        default     => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('user_type')
                    ->label('نوع المستخدم')
                    ->options([
                        'admin'     => 'مدير',
                        'company'   => 'شركة',
                        'candidate' => 'باحث عن عمل',
                    ]),
                SelectFilter::make('status')
                    ->label('حالة الحساب')
                    ->options([
                        'active'    => 'نشط',
                        'pending'   => 'قيد التفعيل',
                        'suspended' => 'موقوف',
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
