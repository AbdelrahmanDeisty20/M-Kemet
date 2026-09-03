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
                TextInput::make('name_ar')
                    ->label('اسم المهنة بالعربية')
                    ->required()
                    ->maxLength(255),
                TextInput::make('name_en')
                    ->label('اسم المهنة بالإنجليزية')
                    ->maxLength(255),
                TextInput::make('category')
                    ->label('التصنيف')
                    ->required()
                    ->default('عام')
                    ->maxLength(100),
                Toggle::make('is_active')
                    ->label('نشطة')
                    ->required()
                    ->default(true),
            ]);
    }
}
