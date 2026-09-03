<?php

namespace App\Filament\Resources\Qualifications\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class QualificationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('تفاصيل المؤهل الدراسي')
                    ->icon('heroicon-o-academic-cap')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name_ar')
                            ->label('اسم المؤهل (عربي)'),
                        TextEntry::make('name_en')
                            ->label('اسم المؤهل (إنجليزي)'),
                        TextEntry::make('code')
                            ->label('الكود المختصر')
                            ->badge()
                            ->color('gray')
                            ->placeholder('-'),
                        TextEntry::make('sort_order')
                            ->label('ترتيب العرض')
                            ->numeric(),
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
