<?php

namespace App\Filament\Resources\Professions;

use App\Filament\Resources\Professions\Pages\CreateProfession;
use App\Filament\Resources\Professions\Pages\EditProfession;
use App\Filament\Resources\Professions\Pages\ListProfessions;
use App\Filament\Resources\Professions\Pages\ViewProfession;
use App\Filament\Resources\Professions\Schemas\ProfessionForm;
use App\Filament\Resources\Professions\Schemas\ProfessionInfolist;
use App\Filament\Resources\Professions\Tables\ProfessionsTable;
use App\Models\Profession;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProfessionResource extends Resource
{
    protected static ?string $model = Profession::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name_ar';

    public static function getNavigationGroup(): ?string
    {
        return __('admin.data_references');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.professions');
    }

    public static function getModelLabel(): string
    {
        return __('admin.profession');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.professions');
    }

    public static function form(Schema $schema): Schema
    {
        return ProfessionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProfessionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProfessionsTable::configure($table);
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
            'index' => ListProfessions::route('/'),
            'create' => CreateProfession::route('/create'),
            'view' => ViewProfession::route('/{record}'),
            'edit' => EditProfession::route('/{record}/edit'),
        ];
    }
}
