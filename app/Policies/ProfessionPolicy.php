<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Profession;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProfessionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Profession');
    }

    public function view(AuthUser $authUser, Profession $profession): bool
    {
        return $authUser->can('View:Profession');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Profession');
    }

    public function update(AuthUser $authUser, Profession $profession): bool
    {
        return $authUser->can('Update:Profession');
    }

    public function delete(AuthUser $authUser, Profession $profession): bool
    {
        return $authUser->can('Delete:Profession');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Profession');
    }

    public function restore(AuthUser $authUser, Profession $profession): bool
    {
        return $authUser->can('Restore:Profession');
    }

    public function forceDelete(AuthUser $authUser, Profession $profession): bool
    {
        return $authUser->can('ForceDelete:Profession');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Profession');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Profession');
    }

    public function replicate(AuthUser $authUser, Profession $profession): bool
    {
        return $authUser->can('Replicate:Profession');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Profession');
    }

}