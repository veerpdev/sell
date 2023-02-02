<?php

namespace App\Http\Controllers;

use App\Enum\PatientBillingType;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;
use App\Models\Patient;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\AppointmentPreAdmission;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\AppointmentPreAdmissionRequest;
use App\Http\Requests\AppointmentPreAdmissionValidateRequest;
use App\Models\PatientBilling;

class AppointmentPreAdmissionController extends Controller
{
    /**
     * [Pre Admission] - Show Initial Form
     *
     * @param  string  $token
     * @return \Illuminate\Http\Response
     */
    public function show($token)
    {
        $preAdmission = AppointmentPreAdmission::where('token', $token)
            ->first();

        if ($preAdmission == null) {
            return response()->json(
                [
                    'message'   => 'No Matching Pre-Admission',
                ],
                Response::HTTP_NOT_FOUND
            );
        }

        $data = $preAdmission->getAppointmentPreAdmissionData();

        return response()->json(
            [
                'message'   => "Appointment Pre Admission data",
                'data'      => $data,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * [Pre Admission] - Validate Pre Admission
     *
     * @param  string  $token
     * @return \Illuminate\Http\Response
     */
    public function validatePreAdmission(AppointmentPreAdmissionValidateRequest $request, $token)
    {
        $preAdmission = AppointmentPreAdmission::where('token', $token)->first();

        if ($preAdmission == null) {
            return response()->json(
                [
                    'message'   => 'No Matching Pre-Admission',
                ],
                Response::HTTP_NOT_FOUND
            );
        }

        $appointment = $preAdmission->appointment;
        $patient = $appointment->patient;

        $dateOfBirth = Carbon::create($request->date_of_birth)->format('Y-m-d');
        $lastName = $request->last_name;

        $compareBirthday = Carbon::parse($patient->date_of_birth)->format('Y-m-d');
        if (
            $compareBirthday != $dateOfBirth
            || strtolower($patient->last_name) != strtolower($lastName)
        ) {
            return response()->json(
                [
                    'message'   => 'Please check you have entered your Date of birth and Last name correctly.',
                ],
                Response::HTTP_UNPROCESSABLE_ENTITY //Equivalent to a validation error
            );
        }

        $data = $preAdmission->getAppointmentPreAdmissionData();
        $preAdmission->status = 'VALIDATED';
        $preAdmission->save();

        return response()->json(
            [
                'message'   => 'Appointment Pre Admission validated',
                'data'      => $data,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * [Pre Admission] - Create Pre Admission
     *
     * @param  string  $token
     * @return \Illuminate\Http\Response
     */
    public function store(AppointmentPreAdmissionRequest $request, $token)
    {
        $preAdmission = AppointmentPreAdmission::where('token', $token)->first();

        if ($preAdmission == null) {
            return response()->json(
                [
                    'message'   => 'Appointment Pre Admission created',
                    'data'      => null,
                ],
                Response::HTTP_OK
            );
        }

        if ($preAdmission->status != 'VALIDATED') {
            $data = $preAdmission->getAppointmentPreAdmissionData();

            return response()->json(
                [
                    'message'   => 'Appointment Pre Admission',
                    'data'      => $data,
                ],
                Response::HTTP_OK
            );
        }

        $appointment = $preAdmission->appointment;
        $patient = $appointment->patient;

        Patient::where('id', $appointment->patient_id)->update($request->safe()->only($patient->getFillable()));

        $existingMedicareCard = PatientBilling::where('patient_id', $patient->id)
            ->where('member_number', $request->medicare_number)
            ->where('member_reference_number', $request->medicare_reference_number)
            ->where('is_valid', true)
            ->first();

        if (!$existingMedicareCard) {
            PatientBilling::create([
                'patient_id'              => $patient->id,
                'member_number'           => $request->medicare_number,
                'member_reference_number' => $request->medicare_reference_number,
                'is_valid'                => true,
                'verified_at'             => now(),
                'has_medicare_concession' => false,
                'billing_type'            => PatientBillingType::MEDICARE_CARD,
            ]);
        }

        $preAdmissionAnswers = json_decode($request->pre_admission_answers);

        $data = [
            'title' => 'Pre-admission form: ' . $appointment->patient_name['full'],
            'date' => date('d/m/Y'),
            'preadmissionData' => $preAdmissionAnswers,
        ];


        $pdf = PDF::loadView('pdfs/patientPreAdmissionForm', $data);


        $fileName = saveFile($pdf, true);

        $preAdmission->pre_admission_file = $fileName;
        $preAdmission->status = 'CREATED';
        $preAdmission->save();



        return response()->json(
            [
                'message'   => 'Appointment Pre Admission',
                'data'      => $data,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * [Pre Admission] - Upload Pre Admission
     *
     * @param  string  $token
     * @return \Illuminate\Http\Response
     */
    public function upload(Request $request, Appointment $appointment)
    {
        $preAdmission = $appointment->pre_admission;

        // Verify the user can access this function via policy
        $this->authorize('update', $preAdmission);

        if ($file = $request->file('file')) {
            $fileName = saveFile($file);
            $preAdmission->pre_admission_file = $fileName;
            $preAdmission->save();
        }

        return response()->json(
            [
                'message' => 'Pre Admission File Uploaded',
                'data'    => $appointment,
            ],
            Response::HTTP_OK
        );
    }
}
