<?php

namespace App\Filament\Resources\AppNotifications;

use App\Filament\Resources\AppNotifications\Pages\CreateAppNotification;
use App\Filament\Resources\AppNotifications\Pages\EditAppNotification;
use App\Filament\Resources\AppNotifications\Pages\ListAppNotifications;
use App\Filament\Resources\AppNotifications\Pages\ViewAppNotification;
use App\Filament\Resources\AppNotifications\Schemas\AppNotificationForm;
use App\Filament\Resources\AppNotifications\Schemas\AppNotificationInfolist;
use App\Filament\Resources\AppNotifications\Tables\AppNotificationsTable;
use App\Models\AppNotification;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AppNotificationResource extends Resource
{
    protected static ?string $model = AppNotification::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBell;

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'title_ar';

    public static function getNavigationGroup(): ?string
    {
        return __('admin.user_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.app_notifications');
    }

    public static function getModelLabel(): string
    {
        return __('admin.app_notification');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.app_notifications');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title_ar', 'title_en', 'message_ar', 'message_en'];
    }

    public static function form(Schema $schema): Schema
    {
        return AppNotificationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AppNotificationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AppNotificationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListAppNotifications::route('/'),
            'create' => CreateAppNotification::route('/create'),
            'view'   => ViewAppNotification::route('/{record}'),
            'edit'   => EditAppNotification::route('/{record}/edit'),
        ];
    }
}
