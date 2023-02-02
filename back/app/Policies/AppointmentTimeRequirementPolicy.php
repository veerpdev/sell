<?php

namespace App\Policies;

use App\Models\AppointmentTimeRequirement;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AppointmentTimeRequirementPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->hasAnyRole(['organizationManager', 'receptionist', 'anesthetist']);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AppointmentTimeRequirement  $appointmentTimeRequirement
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
    public function create(User $user)
    {
        return $user->hasAnyRole(['organizationManager']);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AppointmentTimeRequirement  $appointmentTimeRequirement
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, AppointmentTimeRequirement $appointmentTimeRequirement)
    {
        return $user->hasAnyRole(['organizationManager']) && $appointmentTimeRequirement->organization_id == $user->organization->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AppointmentTimeRequirement  $appointmentTimeRequirement
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, AppointmentTimeRequirement $appointmentTimeRequirement)
    {
        return $user->hasAnyRole(['organizationManager']) && $appointmentTimeRequirement->organization_id == $user->organization->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AppointmentTimeRequirement  $appointmentTimeRequirement
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
     * @param  \App\Models\AppointmentTimeRequirement  $appointmentTimeRequirement
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete()
    {
        return false;
    }
}
