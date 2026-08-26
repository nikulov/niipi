<?php

namespace App\Policies;

use App\Models\MediaFile;
use App\Models\User;

class MediaFilePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isEditorOrViewer($user);
    }

    public function view(User $user, MediaFile $mediaFile): bool
    {
        return $this->isEditorOrViewer($user);
    }

    public function create(User $user): bool
    {
        return $this->isEditor($user);
    }

    public function update(User $user, MediaFile $mediaFile): bool
    {
        return $this->isEditor($user);
    }

    public function delete(User $user, MediaFile $mediaFile): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }

    public function forceDelete(User $user, MediaFile $mediaFile): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }
}
