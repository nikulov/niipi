<?php

namespace App\Filament\Resources\Menus;

use App\Enums\UserRole;
use App\Filament\Resources\Menus\Pages\EditMenu;
use App\Filament\Resources\Menus\Schemas\MenuForm;
use App\Filament\Resources\Menus\Schemas\MenuInfolist;
use App\Filament\Resources\Menus\Tables\MenusTable;
use App\Filament\Support\RoleAccessResource;
use App\Models\Menu;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MenuResource extends Resource
{
    use RoleAccessResource;
    
    protected static function allowedRoles(): array
    {
        return [UserRole::Admin];
    }
    
    protected static ?string $model = Menu::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Bars3;

    protected static ?string $recordTitleAttribute = 'Menu';
    
    protected static ?string $navigationLabel = 'Меню';
    
    protected static string|null|\UnitEnum $navigationGroup = 'Настройки';
    
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return MenuForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MenuInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MenusTable::configure($table);
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
            'index' => EditMenu::route('/'),
        ];
    }
}
