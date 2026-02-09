<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

abstract class BasePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->role === UserRole::Admin ? true : null;
    }
    
    protected function isEditor(User $user): bool
    {
        return $user->role === UserRole::Editor;
    }
    
    protected function isViewer(User $user): bool
    {
        return $user->role === UserRole::Viewer;
    }
    
    protected function isEditorOrViewer(User $user): bool
    {
        return in_array($user->role, [UserRole::Editor, UserRole::Viewer], true);
    }
}