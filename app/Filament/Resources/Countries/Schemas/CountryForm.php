<?php

namespace App\Filament\Resources\Countries\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CountryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name_ar')
                    ->label('اسم الدولة بالعربية')
                    ->required()
                    ->maxLength(255),
                TextInput::make('name_en')
                    ->label('اسم الدولة بالإنجليزية')
                    ->required()
                    ->maxLength(255),
                TextInput::make('code')
                    ->label('الكود (ISO)')
                    ->required()
                    ->maxLength(10),
                TextInput::make('flag_icon_path')
                    ->label('مسار أيقونة العلم')
                    ->maxLength(500),
                Toggle::make('is_active')
                    ->label('نشطة')
                    ->required()
                    ->default(true),
            ]);
    }
}
