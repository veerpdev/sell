<?php

namespace App\Policies;

use App\Models\PreAdmissionConsent;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PreAdmissionConsentPolicy
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
    public function viewAny()
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PreAdmissionConsent  $preAdmissionConsent
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, PreAdmissionConsent $preAdmissionConsent)
    {
        return $user->hasAnyRole(['organizationManager']) && $preAdmissionConsent->organization_id == $user->organization->id;
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
     * @param  \App\Models\PreAdmissionConsent  $preAdmissionConsent
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, PreAdmissionConsent $preAdmissionConsent)
    {
        return $user->hasAnyRole(['organizationManager']) && $preAdmissionConsent->organization_id == $user->organization->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PreAdmissionConsent  $preAdmissionConsent
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
     * @param  \App\Models\PreAdmissionConsent  $preAdmissionConsent
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
     * @param  \App\Models\PreAdmissionConsent  $preAdmissionConsent
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete()
    {
        return false;
    }
}
