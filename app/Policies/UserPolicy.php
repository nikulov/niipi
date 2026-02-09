<?php

namespace App\Policies;

use App\Models\User;


class UserPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isViewer($user);
    }
    
    public function view(User $user, User $post): bool
    {
        return $this->isViewer($user);
    }
    
    public function create(User $user): bool
    {
        return false;
    }
    
    public function update(User $user, User $post): bool
    {
        return false;
    }
    
    public function delete(User $user, User $post): bool
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