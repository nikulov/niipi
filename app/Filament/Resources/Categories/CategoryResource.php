<?php

namespace App\Filament\Resources\Categories;

use App\Enums\UserRole;
use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Filament\Resources\Categories\Pages\ViewCategory;
use App\Filament\Resources\Categories\Schemas\CategoryForm;
use App\Filament\Resources\Categories\Schemas\CategoryInfolist;
use App\Filament\Resources\Categories\Tables\CategoriesTable;
use App\Filament\Support\RoleAccessResource;
use App\Models\Category;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CategoryResource extends Resource
{
    use RoleAccessResource;
    
    protected static function allowedRoles(): array
    {
        return [UserRole::Admin, UserRole::Editor, UserRole::Viewer];
    }
    
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    
    public static function getModelLabel(): string
    {
        return __('panel.category');
    }
    
    public static function getPluralModelLabel(): string
    {
        return __('panel.categories');
    }
    
    public static function getNavigationLabel(): string
    {
        return __('panel.categories');
    }
    
    public static function getNavigationGroup(): ?string
    {
        return __('panel.publications');
    }
    
    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return CategoryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CategoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CategoriesTable::configure($table);
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
            'index' => ListCategories::route('/'),
            'create' => CreateCategory::route('/create'),
            'view' => ViewCategory::route('/{record}'),
            'edit' => EditCategory::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount(array_values(Category::typeToRelation()));
    }
}
