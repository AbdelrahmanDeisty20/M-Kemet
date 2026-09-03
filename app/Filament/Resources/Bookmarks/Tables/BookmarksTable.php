<?php

namespace App\Filament\Resources\Bookmarks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BookmarksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('المستخدم / الشركة')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->formatStateUsing(fn ($state, $record) => $record->user?->display_name ?? $state),
                TextColumn::make('candidate.name')
                    ->label('الباحث عن عمل المحفوظ')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                TextColumn::make('created_at')
                    ->label('تاريخ الحفظ')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->recordActions([
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
