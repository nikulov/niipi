<?php
namespace App\Filament\Support;

use App\Enums\UserRole;
use Filament\Facades\Filament;

trait RoleAccessResource
{
    protected static function allowedRoles(): array
    {
        return [UserRole::Admin];
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        $user = Filament::auth()->user();
        
        return $user && in_array($user->role, static::allowedRoles(), true);
    }
    
    public static function canViewAny(): bool
    {
        $user = Filament::auth()->user();
        
        return $user && in_array($user->role, static::allowedRoles(), true);
    }
}