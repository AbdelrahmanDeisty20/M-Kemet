<?php

namespace App\Filament\Resources\Countries\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CountryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات الدولة')
                    ->icon('heroicon-o-globe-alt')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name_ar')
                            ->label('اسم الدولة (عربي)'),
                        TextEntry::make('name_en')
                            ->label('اسم الدولة (إنجليزي)'),
                        TextEntry::make('code')
                            ->label('كود الدولة (ISO)')
                            ->badge()
                            ->color('gray'),
                        IconEntry::make('is_active')
                            ->label('حالة التفعيل')
                            ->boolean(),
                    ]),

                Section::make('سجل النظام')
                    ->icon('heroicon-o-clock')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('تاريخ الإضافة')
                            ->dateTime('Y-m-d H:i:s'),
                        TextEntry::make('updated_at')
                            ->label('آخر تحديث')
                            ->dateTime('Y-m-d H:i:s'),
                    ]),
            ]);
    }
}
