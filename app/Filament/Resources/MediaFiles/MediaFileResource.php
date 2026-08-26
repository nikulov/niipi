<?php

namespace App\Filament\Resources\MediaFiles;

use App\Enums\UserRole;
use App\Filament\Resources\MediaFiles\Pages\CreateMediaFile;
use App\Filament\Resources\MediaFiles\Pages\EditMediaFile;
use App\Filament\Resources\MediaFiles\Pages\ListMediaFiles;
use App\Filament\Resources\MediaFiles\Schemas\MediaFileForm;
use App\Filament\Resources\MediaFiles\Tables\MediaFilesTable;
use App\Filament\Support\RoleAccessResource;
use App\Models\MediaFile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MediaFileResource extends Resource
{
    use RoleAccessResource;

    protected static ?string $model = MediaFile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::FolderOpen;

    protected static ?int $navigationSort = 1;

    protected static function allowedRoles(): array
    {
        return [UserRole::Admin, UserRole::Editor, UserRole::Viewer];
    }

    public static function getModelLabel(): string
    {
        return __('panel.media_file');
    }

    public static function getPluralModelLabel(): string
    {
        return __('panel.media_files');
    }

    public static function getNavigationLabel(): string
    {
        return __('panel.media_files_list');
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Медиа';
    }

    public static function form(Schema $schema): Schema
    {
        return MediaFileForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MediaFilesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMediaFiles::route('/'),
            'create' => CreateMediaFile::route('/create'),
            'edit' => EditMediaFile::route('/{record}/edit'),
        ];
    }
}
