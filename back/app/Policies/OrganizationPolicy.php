<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class OrganizationPolicy
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
    public function viewAny(User $user)
    {
        return $user->hasAnyRole(['admin']);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Organization  $organization
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Organization $organization)
    {
        return $user->organization->id == $organization->id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create()
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Organization  $organization
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Organization $organization)
    {
        return $user->hasAnyRole(['organizationManager']) && $organization->id === $user->organization_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Organization  $organization
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete()
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Organization  $organization
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
     * @param  \App\Models\Organization  $organization
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete()
    {
        return false;
    }

    /**
     * Determine whether the user is able to manage the organization
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Organization  $organization
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function manage(User $user, Organization $organization)
    {
        return $user->organization->id === $organization->id
            ? Response::allow()
            : Response::deny('Different Organization');
    }
}
