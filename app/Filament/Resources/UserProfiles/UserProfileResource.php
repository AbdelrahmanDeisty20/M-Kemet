<?php

namespace App\Filament\Resources\UserProfiles;

use App\Filament\Resources\UserProfiles\Pages\CreateUserProfile;
use App\Filament\Resources\UserProfiles\Pages\EditUserProfile;
use App\Filament\Resources\UserProfiles\Pages\ListUserProfiles;
use App\Filament\Resources\UserProfiles\Pages\ViewUserProfile;
use App\Filament\Resources\UserProfiles\Schemas\UserProfileForm;
use App\Filament\Resources\UserProfiles\Schemas\UserProfileInfolist;
use App\Filament\Resources\UserProfiles\Tables\UserProfilesTable;
use App\Models\UserProfile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserProfileResource extends Resource
{
    protected static ?string $model = UserProfile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'qualification';

    public static function getNavigationGroup(): ?string
    {
        return __('admin.user_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.candidates');
    }

    public static function getModelLabel(): string
    {
        return __('admin.candidate');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.candidates');
    }

    public static function form(Schema $schema): Schema
    {
        return UserProfileForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserProfileInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserProfilesTable::configure($table);
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
            'index' => ListUserProfiles::route('/'),
            'create' => CreateUserProfile::route('/create'),
            'view' => ViewUserProfile::route('/{record}'),
            'edit' => EditUserProfile::route('/{record}/edit'),
        ];
    }
}
