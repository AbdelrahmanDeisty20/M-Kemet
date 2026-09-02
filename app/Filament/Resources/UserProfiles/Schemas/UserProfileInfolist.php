<?php

namespace App\Filament\Resources\UserProfiles\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserProfileInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')
                    ->label('User'),
                TextEntry::make('birth_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('gender')
                    ->badge(),
                TextEntry::make('gender.id')
                    ->label('Gender')
                    ->placeholder('-'),
                TextEntry::make('currentCountry.id')
                    ->label('Current country')
                    ->placeholder('-'),
                TextEntry::make('qualification')
                    ->placeholder('-'),
                TextEntry::make('sub_specialization')
                    ->placeholder('-'),
                TextEntry::make('profession.id')
                    ->label('Profession')
                    ->placeholder('-'),
                TextEntry::make('experience_years')
                    ->numeric(),
                TextEntry::make('experienceLevel.id')
                    ->label('Experience level')
                    ->placeholder('-'),
                TextEntry::make('expected_salary')
                    ->numeric()
                    ->placeholder('-'),
                IconEntry::make('willing_to_travel')
                    ->boolean(),
                TextEntry::make('summary')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('rejection_reason')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
