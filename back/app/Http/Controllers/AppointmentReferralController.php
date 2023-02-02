<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Appointment;
use Illuminate\Http\Response;
use App\Models\PatientDocument;
use App\Http\Requests\AppointmentReferralRequest;

class AppointmentReferralController extends Controller
{
    /**
     * [Referral] - Update
     *
     * @group Appointments
     * @param  \App\Http\Requests\AppointmentReferralRequest  $request
     * @param  \App\Models\Appointment  $appointment
     * @return \Illuminate\Http\Response
     */
    public function update(
        AppointmentReferralRequest $request,
        Appointment $appointment
    ) {
        $appointmentReferral = $appointment->referral;

        // Verify the user can access this function via policy
        $this->authorize('update', $appointmentReferral);

        $appointmentReferral->update([
            'doctor_address_book_id'   => $request->doctor_address_book_id,
            'referral_date'         =>  Carbon::create($request->referral_date)->toDateString(),
            'referral_duration'     => $request->referral_duration,
            'referral_expiry_date'  =>  Carbon::create($request->referral_date)
                ->addMonths($request->referral_duration)
                ->toDateString(),
        ]);

        if ($appointmentReferral->patient_document) {
            $patientDocument = $appointmentReferral->patient_document;
        } else {
            $userId = auth()->user()->id;
            $patient = $appointment->patient;

            $data = [
                ...$request->all(),
                'patient_id'    => $patient->id,
                'document_type' => 'REFERRAL',
                'created_by'    => $userId,
                'organization_id' => auth()->user()->organization_id,
            ];
            $patientDocument = PatientDocument::create($data);
        }

        if ($file = $request->file('file')) {
            $fileName = saveFile($file);
            $patientDocument->file_path = $fileName;
            $patientDocument->save();
        }

        $appointmentReferral->patient_document_id = $patientDocument->id;
        $appointmentReferral->save();

        return response()->json(
            [
                'message'   => 'Appointment Referral Info updated',
                'data'      => $appointmentReferral,
            ],
            Response::HTTP_OK
        );
    }
}
