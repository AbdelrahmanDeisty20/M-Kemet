<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CompanyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات الشركة الأساسية')
                    ->icon('heroicon-o-building-office')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('company_name')
                            ->label('اسم الشركة')
                            ->placeholder('-'),
                        TextEntry::make('commercial_register_number')
                            ->label('رقم السجل التجاري')
                            ->placeholder('-'),
                        TextEntry::make('industry')
                            ->label('قطاع النشاط')
                            ->badge()
                            ->color('warning')
                            ->placeholder('-'),
                        TextEntry::make('user.name')
                            ->label('المستخدم المسؤول')
                            ->placeholder('-'),
                        TextEntry::make('user.phone')
                            ->label('رقم الهاتف')
                            ->placeholder('-'),
                    ]),

                Section::make('حالة واعتماد الشركة')
                    ->icon('heroicon-o-check-badge')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('status')
                            ->label('حالة الشركة')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'approved' => 'success',
                                'pending'  => 'warning',
                                'rejected' => 'danger',
                                default    => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'approved' => 'معتمدة',
                                'pending'  => 'قيد المراجعة',
                                'rejected' => 'مرفوضة',
                                default    => $state,
                            }),
                        TextEntry::make('rejection_reason')
                            ->label('سبب الرفض')
                            ->placeholder('لا يوجد')
                            ->visible(fn ($record) => $record?->status === 'rejected')
                            ->columnSpanFull(),
                    ]),

                Section::make('التواريخ والسجلات')
                    ->icon('heroicon-o-clock')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('تاريخ التسجيل')
                            ->dateTime('Y-m-d H:i:s'),
                        TextEntry::make('updated_at')
                            ->label('آخر تحديث')
                            ->dateTime('Y-m-d H:i:s'),
                    ]),
            ]);
    }
}
