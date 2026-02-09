<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isEditorOrViewer($user);
    }
    
    public function view(User $user, Category $category): bool
    {
        return $this->isEditorOrViewer($user);
    }
    
    public function create(User $user): bool
    {
        return $this->isEditor($user);
    }
    
    public function update(User $user, Category $category): bool
    {
        return $this->isEditor($user);
    }
    
    public function delete(User $user, Category $category): bool
    {
        return false;
    }
    
    public function deleteAny(User $user): bool
    {
        return false;
    }
    
    public function forceDelete(User $user, Post $post): bool
    {
        return false;
    }
    
    public function forceDeleteAny(User $user): bool
    {
        return false;
    }
}