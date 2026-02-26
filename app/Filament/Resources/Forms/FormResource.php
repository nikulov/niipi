<?php

namespace App\Filament\Resources\Forms;

use App\Enums\UserRole;
use App\Filament\Resources\Forms\Pages\CreateForm;
use App\Filament\Resources\Forms\Pages\EditForm;
use App\Filament\Resources\Forms\Pages\ListForms;
use App\Filament\Resources\Forms\RelationManagers\FieldsRelationManager;
use App\Filament\Resources\Forms\Schemas\FormForm;
use App\Filament\Resources\Forms\Tables\FormsTable;
use App\Filament\Support\RoleAccessResource;
use App\Models\Form;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FormResource extends Resource
{
    use RoleAccessResource;
    
    protected static function allowedRoles(): array
    {
        return [UserRole::Admin, UserRole::Viewer];
    }
    
    protected static ?string $model = Form::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentCheck;
    
    public static function getModelLabel(): string
    {
        return __('panel.form');
    }
    
    public static function getPluralModelLabel(): string
    {
        return __('panel.forms');
    }
    
    public static function getNavigationLabel(): string
    {
        return __('panel.forms_list');
    }
    
    public static function getNavigationGroup(): ?string
    {
        return __('panel.forms');
    }

    public static function form(Schema $schema): Schema
    {
        return FormForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FormsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            FieldsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListForms::route('/'),
            'create' => CreateForm::route('/create'),
            'edit' => EditForm::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount(['fields', 'submissions']);
    }
}
