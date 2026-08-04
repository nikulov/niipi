<?php

namespace App\Policies;

use App\Models\GlobalSetting;
use App\Models\User;

class GlobalSettingPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, GlobalSetting $globalSetting): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, GlobalSetting $globalSetting): bool
    {
        return false;
    }

    public function delete(User $user, GlobalSetting $globalSetting): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }

    public function forceDelete(User $user, GlobalSetting $globalSetting): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }
}
