<?php

namespace App\Filament\Resources\Documents;

use App\Filament\Resources\Documents\Pages\CreateDocument;
use App\Filament\Resources\Documents\Pages\EditDocument;
use App\Filament\Resources\Documents\Pages\ListDocuments;
use App\Filament\Resources\Documents\Pages\ViewDocument;
use App\Filament\Resources\Documents\Schemas\DocumentForm;
use App\Filament\Resources\Documents\Schemas\DocumentInfolist;
use App\Filament\Resources\Documents\Tables\DocumentsTable;
use App\Models\User;
use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DocumentResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return 'إدارة المحتوى والمستندات';
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.documents');
    }

    public static function getModelLabel(): string
    {
        return __('admin.document');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.documents');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where(fn ($query) => $query->whereHas('documents')->orWhereHas('video'))
            ->withCount('documents')
            ->with(['documents', 'video']);
    }

    public static function form(Schema $schema): Schema
    {
        return DocumentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DocumentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DocumentsTable::configure($table);
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
            'index' => ListDocuments::route('/'),
            'create' => CreateDocument::route('/create'),
            'view' => ViewDocument::route('/{record}'),
            'edit' => EditDocument::route('/{record}/edit'),
        ];
    }

    public static function deleteUserDocuments(User $user): void
    {
        foreach ($user->documents as $doc) {
            static::deleteSingleDocument($doc);
        }

        if ($user->video) {
            static::deleteSingleVideo($user->video);
        }
    }

    public static function deleteSingleDocument(\App\Models\Document $doc): void
    {
        if ($doc->file_path) {
            $disk = $doc->disk ?? 'public';
            $cleanPath = ltrim(str_replace(['public/', 'storage/'], '', $doc->file_path), '/');
            if (\Illuminate\Support\Facades\Storage::disk($disk)->exists($cleanPath)) {
                \Illuminate\Support\Facades\Storage::disk($disk)->delete($cleanPath);
            }
            if ($disk !== 'public' && \Illuminate\Support\Facades\Storage::disk('public')->exists($cleanPath)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($cleanPath);
            }
        }
        $doc->delete();
    }

    public static function deleteSingleVideo(\App\Models\Video $video): void
    {
        if ($video->video_path) {
            $cleanPath = ltrim(str_replace(['public/', 'storage/'], '', $video->video_path), '/');
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($cleanPath)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($cleanPath);
            }
        }
        if ($video->thumbnail_path) {
            $cleanPath = ltrim(str_replace(['public/', 'storage/'], '', $video->thumbnail_path), '/');
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($cleanPath)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($cleanPath);
            }
        }
        $video->delete();
    }
}
