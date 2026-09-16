<?php

namespace App\Filament\Resources\AppNotifications\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AppNotificationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('admin.target_user'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->placeholder('جميع المستخدمين (إرسال عام)'),
                TextColumn::make('title_ar')
                    ->label(__('admin.title_ar'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('admin.notification_type'))
                    ->badge()
                    ->color('info')
                    ->sortable(),
                IconColumn::make('is_read')
                    ->label(__('admin.is_read'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.created_at'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label(__('admin.target_user'))
                    ->relationship('user', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => ($record->name ?? $record->email) . ' (' . ($record->email ?? '') . ')')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_read')
                    ->label(__('admin.is_read')),
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
