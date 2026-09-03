<?php

namespace App\Filament\Resources\Videos\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class VideoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات صاحب الفيديو')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->schema([
                        Select::make('user_id')
                            ->label('المرشح / المستخدم')
                            ->relationship('user', 'name', modifyQueryUsing: fn ($query) => $query->select(['id', 'name', 'phone', 'email']))
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name ?: ($record->phone ?: ($record->email ?: "مستخدم #{$record->id}")))
                            ->searchable(['name', 'phone', 'email'])
                            ->preload()
                            ->required(),
                        TextInput::make('video_path')
                            ->label('مسار الفيديو')
                            ->required(),
                    ]),

                Section::make('حالة تفعيل واعتماد الفيديو')
                    ->icon('heroicon-o-check-badge')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->label('حالة الفيديو')
                            ->options([
                                'pending'  => 'قيد المراجعة',
                                'approved' => 'معتمد',
                                'rejected' => 'مرفوض',
                            ])
                            ->default('pending')
                            ->required(),
                        Textarea::make('rejection_reason')
                            ->label('سبب الرفض (في حالة الرفض)')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
