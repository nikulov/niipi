<?php

namespace App\Filament\Resources\FormSubmissions;

use App\Enums\UserRole;
use App\Filament\Resources\FormSubmissions\Pages\CreateFormSubmission;
use App\Filament\Resources\FormSubmissions\Pages\EditFormSubmission;
use App\Filament\Resources\FormSubmissions\Pages\ListFormSubmissions;
use App\Filament\Resources\FormSubmissions\Schemas\FormSubmissionForm;
use App\Filament\Resources\FormSubmissions\Tables\FormSubmissionsTable;
use App\Filament\Support\RoleAccessResource;
use App\Models\FormSubmission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FormSubmissionResource extends Resource
{
    use RoleAccessResource;
    
    protected static function allowedRoles(): array
    {
        return [UserRole::Admin, UserRole::Viewer];
    }
    
    protected static ?string $model = FormSubmission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Square3Stack3d;
    
    public static function getModelLabel(): string
    {
        return __('panel.submission');
    }
    
    public static function getPluralModelLabel(): string
    {
        return __('panel.submissions');
    }
    
    public static function getNavigationLabel(): string
    {
        return __('panel.submissions_list');
    }
    
    public static function getNavigationGroup(): ?string
    {
        return __('panel.forms');
    }

    public static function form(Schema $schema): Schema
    {
        return FormSubmissionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FormSubmissionsTable::configure($table);
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
            'index' => ListFormSubmissions::route('/'),
            'create' => CreateFormSubmission::route('/create'),
            'edit' => EditFormSubmission::route('/{record}/edit'),
        ];
    }
}
