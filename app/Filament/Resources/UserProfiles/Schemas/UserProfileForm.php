<?php

namespace App\Filament\Resources\UserProfiles\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('البيانات العامة والشخصية')
                    ->icon('heroicon-o-user')
                    ->columns(3)
                    ->schema([
                        Select::make('user_id')
                            ->label('الباحث عن عمل (المستخدم)')
                            ->relationship('user', 'name', modifyQueryUsing: fn ($query) => $query->select(['id', 'name', 'phone', 'email']))
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name ?: ($record->phone ?: ($record->email ?: "مستخدم #{$record->id}")))
                            ->searchable(['name', 'phone', 'email'])
                            ->preload()
                            ->required(),
                        DatePicker::make('birth_date')
                            ->label('تاريخ الميلاد'),
                        Select::make('gender_id')
                            ->label('الجنس')
                            ->relationship('genderRelation', 'name_ar')
                            ->preload(),
                        Select::make('current_country_id')
                            ->label('الدولة الحالية')
                            ->relationship('currentCountry', 'name_ar')
                            ->searchable()
                            ->preload(),
                    ]),

                Section::make('البيانات المهنية والمؤهلات')
                    ->icon('heroicon-o-briefcase')
                    ->columns(3)
                    ->schema([
                        Select::make('qualification_id')
                            ->label('المؤهل الدراسي')
                            ->relationship('qualification', 'name_ar')
                            ->searchable()
                            ->preload(),
                        TextInput::make('sub_specialization')
                            ->label('التخصص الفرعي')
                            ->maxLength(255),
                        Select::make('profession_id')
                            ->label('المهنة الرئيسية')
                            ->relationship('profession', 'name_ar')
                            ->searchable()
                            ->preload(),
                        TextInput::make('experience_years')
                            ->label('سنوات الخبرة')
                            ->numeric()
                            ->default(0)
                            ->required(),
                        Select::make('experience_level_id')
                            ->label('مستوى الخبرة')
                            ->relationship('experienceLevel', 'name_ar')
                            ->preload(),
                        TextInput::make('expected_salary')
                            ->label('الراتب المتوقع')
                            ->numeric(),
                        Toggle::make('willing_to_travel')
                            ->label('مستعد للسفر بالخارج')
                            ->default(false),
                    ]),

                Section::make('الملف التعريفي والاعتماد')
                    ->icon('heroicon-o-document-text')
                    ->columns(2)
                    ->schema([
                        Textarea::make('summary')
                            ->label('ملخص الباحث عن عمل')
                            ->rows(3)
                            ->columnSpanFull(),
                        Select::make('status')
                            ->label('حالة الملف')
                            ->options([
                                'pending'  => 'قيد المراجعة',
                                'approved' => 'معتمد',
                                'rejected' => 'مرفوض',
                            ])
                            ->default('pending')
                            ->required(),
                        Textarea::make('rejection_reason')
                            ->label('سبب الرفض')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
