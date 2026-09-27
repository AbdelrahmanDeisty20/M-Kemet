<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات الشركة الأساسية')
                    ->icon('heroicon-o-building-office')
                    ->columns(3)
                    ->schema([
                        Select::make('user_id')
                            ->label('المستخدم المسؤول')
                            ->relationship('user', 'name', modifyQueryUsing: fn ($query) => $query->select(['id', 'name', 'phone']))
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name ?: ($record->phone ?: "مستخدم #{$record->id}"))
                            ->searchable(['name', 'phone'])
                            ->preload()
                            ->required(),
                        TextInput::make('company_name')
                            ->label('اسم الشركة')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('commercial_register_number')
                            ->label('رقم السجل التجاري')
                            ->maxLength(100),
                        TextInput::make('industry')
                            ->label('قطاع النشاط')
                            ->maxLength(255),
                    ]),

                Section::make('اعتماد وتفعيل الشركة')
                    ->icon('heroicon-o-check-badge')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->label('حالة الاعتماد')
                            ->options([
                                'pending'  => 'قيد المراجعة',
                                'approved' => 'معتمدة',
                                'rejected' => 'مرفوضة',
                            ])
                            ->default('pending')
                            ->required(),
                        Textarea::make('rejection_reason')
                            ->label('سبب الرفض (إن وجد)')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
