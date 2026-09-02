<?php

namespace App\Filament\Resources\Professions\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProfessionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title_ar')
                    ->required(),
                TextInput::make('title_en'),
                TextInput::make('category')
                    ->required()
                    ->default('عام'),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
