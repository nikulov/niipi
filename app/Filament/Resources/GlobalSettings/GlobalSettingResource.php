<?php

namespace App\Filament\Resources\GlobalSettings;

use App\Filament\Resources\GlobalSettings\Pages\EditGlobalSetting;
use App\Filament\Resources\GlobalSettings\Schemas\GlobalSettingForm;
use App\Filament\Resources\GlobalSettings\Schemas\GlobalSettingInfolist;
use App\Filament\Resources\GlobalSettings\Tables\GlobalSettingsTable;
use App\Models\GlobalSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GlobalSettingResource extends Resource
{
    protected static ?string $model = GlobalSetting::class;
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;
    
    protected static ?string $recordTitleAttribute = 'GlobalSettings';
    
    protected static ?string $navigationLabel = 'Глобальные настройки';
    
    protected static string|null|\UnitEnum $navigationGroup = 'Настройки';
    
    protected static ?int $navigationSort = 2;
    
    public static function form(Schema $schema): Schema
    {
        return GlobalSettingForm::configure($schema);
    }
    
    public static function infolist(Schema $schema): Schema
    {
        return GlobalSettingInfolist::configure($schema);
    }
    
    public static function table(Table $table): Table
    {
        return GlobalSettingsTable::configure($table);
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
            'index' => EditGlobalSetting::route('/'),
        ];
    }
    
    public static function canCreate(): bool
    {
        return false; // запрещаем создание новых записей
    }
    
    public static function canDelete($record): bool
    {
        return false; // запрещаем удаление
    }
}
