<?php

namespace App\Policies;

use App\Models\User;
use App\Enum\UserRole;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     *
     * @param  \App\Models\User  $user
     * @return void|bool
     */
    public function before(User $user)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user, int $organizationId = null)
    {
        return $user->hasAnyRole(['organizationManager', 'receptionist', 'anesthetist', 'specialist']) && $user->organization->id == $organizationId;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view()
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user, int $organizationId = null, int $roleId = null)
    {
        if ($roleId === UserRole::ADMIN && !$user->hasRole('admin')) {
            return false;
        }
        return $user->hasRole('organizationManager') && $user->organization->id == $organizationId;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, User $model)
    {
        return $user->hasRole('organizationManager') && $user->organization->id == $model->organization->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, User $model)
    {
        return $user->hasRole('organizationManager') && $user->organization->id == $model->organization->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore()
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete()
    {
        return false;
    }

    /**
     * Determine whether the user can update the user profile.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function updateProfile(User $user, User $model)
    {
        // A user can update their own profile
        return ($user->hasRole('organizationManager') && $user->organization->id == $model->organization->id) || $user == $model;
    }

    // Functions related to authorization pins

    /**
     * Determine whether the user can verify a pin
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function verifyPin(User $user, User $model)
    {
        return $user->hasAnyRole(['organizationManager']) && $model->organization->id === $user->organization_id;
    }
}
