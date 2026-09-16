<?php

namespace App\Filament\Resources\AppNotifications\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AppNotificationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('تفاصيل الإشعار المرسل')
                    ->icon('heroicon-o-bell')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('user.name')
                            ->label(__('admin.target_user'))
                            ->badge()
                            ->placeholder('جميع المستخدمين (عام)'),
                        TextEntry::make('type')
                            ->label(__('admin.notification_type'))
                            ->badge()
                            ->color('info'),
                        IconEntry::make('is_read')
                            ->label(__('admin.is_read'))
                            ->boolean(),
                    ]),
                Section::make('محتوى الرسالة (عربي / إنجليزي)')
                    ->icon('heroicon-o-chat-bubble-bottom-center-text')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('title_ar')
                            ->label(__('admin.title_ar')),
                        TextEntry::make('title_en')
                            ->label(__('admin.title_en')),
                        TextEntry::make('message_ar')
                            ->label(__('admin.message_ar'))
                            ->columnSpanFull(),
                        TextEntry::make('message_en')
                            ->label(__('admin.message_en'))
                            ->columnSpanFull(),
                    ]),
                Section::make('التواريخ')
                    ->icon('heroicon-o-clock')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label(__('admin.created_at'))
                            ->dateTime('Y-m-d H:i:s'),
                        TextEntry::make('updated_at')
                            ->label(__('admin.updated_at'))
                            ->dateTime('Y-m-d H:i:s'),
                    ]),
            ]);
    }
}
