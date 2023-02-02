<?php

namespace App\Policies;

use App\Models\PatientMedication;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PatientMedicationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny()
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PatientMedication  $patientMedication
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view()
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->hasAnyRole(['organizationManager', 'receptionist', 'anesthetist', 'specialist']);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PatientMedication  $patientMedication
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, PatientMedication $patientMedication)
    {
        return $user->hasAnyRole(['organizationManager', 'receptionist', 'anesthetist', 'specialist']) && $patientMedication->patient->isPartOfOrganization($user->organization->id);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PatientMedication  $patientMedication
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, PatientMedication $patientMedication)
    {
        return $user->hasAnyRole(['organizationManager', 'receptionist', 'anesthetist', 'specialist']) && $patientMedication->patient->isPartOfOrganization($user->organization->id);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PatientMedication  $patientMedication
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore()
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PatientMedication  $patientMedication
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete()
    {
        //
    }
}
