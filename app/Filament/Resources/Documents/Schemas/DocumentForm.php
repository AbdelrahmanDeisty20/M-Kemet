<?php

namespace App\Filament\Resources\Documents\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات صاحب المستندات')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('اسم المستخدم')
                            ->disabled(),
                        TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->disabled(),
                    ]),

                Section::make('إدارة واعتماد المستندات المرفوعة')
                    ->icon('heroicon-o-document-text')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('documents')
                            ->label('المستندات')
                            ->relationship('documents')
                            ->columns(3)
                            ->schema([
                                Select::make('document_type')
                                    ->label('نوع المستند')
                                    ->options([
                                        'cv'             => 'السيرة الذاتية (CV)',
                                        'national_id'    => 'الهوية الوطنية',
                                        'passport'       => 'جواز السفر',
                                        'personal_photo' => 'صورة شخصية',
                                    ])
                                    ->required(),
                                TextInput::make('file_path')
                                    ->label('مسار الملف')
                                    ->required(),
                                Toggle::make('is_approved')
                                    ->label('معتمد')
                                    ->default(false),
                                Textarea::make('rejection_reason')
                                    ->label('سبب الرفض')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}
