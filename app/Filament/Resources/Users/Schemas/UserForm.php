<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name'),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->tel(),
                Select::make('country_id')
                    ->relationship('country', 'id'),
                Select::make('user_type')
                    ->options(['candidate' => 'Candidate', 'company' => 'Company', 'admin' => 'Admin'])
                    ->default('candidate')
                    ->required(),
                Select::make('status')
                    ->options(['active' => 'Active', 'pending' => 'Pending', 'suspended' => 'Suspended'])
                    ->default('active')
                    ->required(),
                Select::make('roles')
                    ->label('الأدوار (Roles)')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
                TextInput::make('otp_code'),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create'),
            ]);
    }
}
