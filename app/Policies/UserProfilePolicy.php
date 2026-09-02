<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserProfile;

class UserProfilePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view candidates');
    }

    public function view(User $user, UserProfile $userProfile): bool
    {
        return $user->can('view candidates');
    }

    public function create(User $user): bool
    {
        return $user->can('edit candidates');
    }

    public function update(User $user, UserProfile $userProfile): bool
    {
        return $user->can('edit candidates');
    }

    public function delete(User $user, UserProfile $userProfile): bool
    {
        return $user->can('edit candidates');
    }
}
