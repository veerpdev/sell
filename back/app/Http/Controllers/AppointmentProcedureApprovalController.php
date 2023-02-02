<?php

namespace App\Http\Controllers;

use App\Enum\ProcedureApprovalStatus;
use App\Http\Requests\AppointmentProcedureApprovalRequest;
use App\Models\Appointment;



class AppointmentProcedureApprovalController extends Controller
{
    /**
     * [Appointment Procedure Approval] - Update Status
     *
     * @param  \App\Http\Requests\AppointmentProcedureApprovalRequest  $request
     * @param  \App\Models\Appointment  $appointment
     * @return \Illuminate\Http\Response
     */
    public function update(
        AppointmentProcedureApprovalRequest $request,
        Appointment $appointment
    ) {
        // Verify the user can access this function via policy
        $this->authorize('update', $appointment);

        $appointment->procedure_approval_status = $request->procedure_approval_status;
        $appointment->save();
        $preadmission = $appointment->pre_admission;
        $preadmission->note = $request->note;
        $preadmission->save();

        if ($appointment->procedure_approval_status == ProcedureApprovalStatus::APPROVED) {
            $appointment->sendNotification('procedure_approved');
        } else {
            $appointment->sendNotification('procedure_denied');
        }

        return response()->json(
            [
                'message' => 'Appointment procedure approval request Updated',
                'data' => $appointment,
            ],
            200
        );
    }
}
