<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\Project;
use App\Models\User;

class ProjectPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isEditorOrViewer($user);
    }
    
    public function view(User $user, Project $post): bool
    {
        return $this->isEditorOrViewer($user);
    }
    
    public function create(User $user): bool
    {
        return $this->isEditor($user);
    }
    
    public function update(User $user, Project $post): bool
    {
        return $this->isEditor($user);
        // если “только свои”:
        // return $this->isEditor($user) && $post->user_id === $user->id;
    }
    
    public function delete(User $user, Project $post): bool
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