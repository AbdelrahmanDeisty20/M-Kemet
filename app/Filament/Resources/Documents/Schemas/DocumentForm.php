<?php

namespace App\Filament\Resources\Documents\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('document_type')
                    ->options([
            'national_id' => 'National id',
            'passport' => 'Passport',
            'cv' => 'Cv',
            'personal_photo' => 'Personal photo',
        ])
                    ->required(),
                TextInput::make('file_path')
                    ->required(),
                TextInput::make('disk')
                    ->required()
                    ->default('private'),
                Toggle::make('is_approved')
                    ->required(),
                Textarea::make('rejection_reason')
                    ->columnSpanFull(),
            ]);
    }
}
