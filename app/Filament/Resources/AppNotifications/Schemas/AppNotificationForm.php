<?php

namespace App\Filament\Resources\AppNotifications\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AppNotificationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label(__('admin.target_user'))
                    ->relationship('user', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => ($record->name ?? $record->email) . ' (' . ($record->email ?? '') . ')')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->placeholder('جميع المستخدمين (إرسال عام للكل) / All Users'),
                TextInput::make('title_ar')
                    ->label(__('admin.title_ar'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('title_en')
                    ->label(__('admin.title_en'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('type')
                    ->label(__('admin.notification_type'))
                    ->default('general')
                    ->required()
                    ->maxLength(255),
                Textarea::make('message_ar')
                    ->label(__('admin.message_ar'))
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('message_en')
                    ->label(__('admin.message_en'))
                    ->required()
                    ->columnSpanFull(),
                Toggle::make('is_read')
                    ->label(__('admin.is_read'))
                    ->default(false),
            ]);
    }
}
