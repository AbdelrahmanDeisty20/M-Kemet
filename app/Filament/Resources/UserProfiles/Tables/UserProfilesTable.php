<?php

namespace App\Filament\Resources\UserProfiles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserProfilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('birth_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('gender')
                    ->badge(),
                TextColumn::make('gender.id')
                    ->searchable(),
                TextColumn::make('currentCountry.id')
                    ->searchable(),
                TextColumn::make('qualification')
                    ->searchable(),
                TextColumn::make('sub_specialization')
                    ->searchable(),
                TextColumn::make('profession.id')
                    ->searchable(),
                TextColumn::make('experience_years')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('experienceLevel.id')
                    ->searchable(),
                TextColumn::make('expected_salary')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('willing_to_travel')
                    ->boolean(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
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
