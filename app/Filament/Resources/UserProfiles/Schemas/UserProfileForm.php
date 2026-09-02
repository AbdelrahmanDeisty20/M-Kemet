<?php

namespace App\Filament\Resources\UserProfiles\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                DatePicker::make('birth_date'),
                Select::make('gender')
                    ->options(['male' => 'Male', 'female' => 'Female'])
                    ->default('male')
                    ->required(),
                Select::make('gender_id')
                    ->relationship('gender', 'id'),
                Select::make('current_country_id')
                    ->relationship('currentCountry', 'id'),
                TextInput::make('qualification'),
                TextInput::make('sub_specialization'),
                Select::make('profession_id')
                    ->relationship('profession', 'id'),
                TextInput::make('experience_years')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('experience_level_id')
                    ->relationship('experienceLevel', 'id'),
                TextInput::make('expected_salary')
                    ->numeric(),
                Toggle::make('willing_to_travel')
                    ->required(),
                TextInput::make('languages'),
                TextInput::make('skills'),
                Textarea::make('summary')
                    ->columnSpanFull(),
                Select::make('status')
                    ->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'])
                    ->default('pending')
                    ->required(),
                Textarea::make('rejection_reason')
                    ->columnSpanFull(),
            ]);
    }
}
