<?php

namespace App\Filament\Resources\Bookmarks;

use App\Filament\Resources\Bookmarks\Pages\ListBookmarks;
use App\Filament\Resources\Bookmarks\Tables\BookmarksTable;
use App\Models\Bookmark;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BookmarkResource extends Resource
{
    protected static ?string $model = Bookmark::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookmark;

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.user_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.bookmarks');
    }

    public static function getModelLabel(): string
    {
        return __('admin.bookmark');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.bookmarks');
    }

    public static function table(Table $table): Table
    {
        return BookmarksTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBookmarks::route('/'),
        ];
    }
}
