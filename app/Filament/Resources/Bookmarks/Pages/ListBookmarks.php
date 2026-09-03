<?php

namespace App\Filament\Resources\Bookmarks\Pages;

use App\Filament\Resources\Bookmarks\BookmarkResource;
use Filament\Resources\Pages\ListRecords;

class ListBookmarks extends ListRecords
{
    protected static string $resource = BookmarkResource::class;
}
