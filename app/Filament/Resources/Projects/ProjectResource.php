<?php

namespace App\Filament\Resources\Projects;

use App\Enums\UserRole;
use App\Filament\Resources\Projects\Pages\CreateProject;
use App\Filament\Resources\Projects\Pages\EditProject;
use App\Filament\Resources\Projects\Pages\ListProjects;
use App\Filament\Resources\Projects\Schemas\ProjectForm;
use App\Filament\Resources\Projects\Tables\ProjectsTable;
use App\Filament\Support\RoleAccessResource;
use App\Models\Project;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProjectResource extends Resource
{
    use RoleAccessResource;
    
    protected static function allowedRoles(): array
    {
        return [UserRole::Admin, UserRole::Editor, UserRole::Viewer];
    }
    
    protected static ?string $model = Project::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Briefcase;
    
    public static function getModelLabel(): string
    {
        return __('panel.projects');
    }
    
    public static function getPluralModelLabel(): string
    {
        return __('panel.projects');
    }
    
    public static function getNavigationLabel(): string
    {
        return __('panel.projects_list');
    }
    
    public static function getNavigationGroup(): ?string
    {
        return __('panel.publications');
    }
    
    protected static ?int $navigationSort = 2;
    

    public static function form(Schema $schema): Schema
    {
        return ProjectForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProjectsTable::configure($table);
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
            'index' => ListProjects::route('/'),
            'create' => CreateProject::route('/create'),
            'edit' => EditProject::route('/{record}/edit'),
        ];
    }
}
