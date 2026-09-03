<?php

namespace App\Filament\Resources\Professions\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProfessionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('تفاصيل المهنة والتخصص')
                    ->icon('heroicon-o-briefcase')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name_ar')
                            ->label('الاسم بالعربية'),
                        TextEntry::make('name_en')
                            ->label('الاسم بالإنجليزية')
                            ->placeholder('-'),
                        TextEntry::make('category')
                            ->label('التصنيف الرئيسي')
                            ->badge()
                            ->color('info'),
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
