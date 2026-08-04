<?php

namespace App\Policies;

use App\Models\Footer;
use App\Models\User;

class FooterPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Footer $footer): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Footer $footer): bool
    {
        return false;
    }

    public function delete(User $user, Footer $footer): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }

    public function forceDelete(User $user, Footer $footer): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }
}
