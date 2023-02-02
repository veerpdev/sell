<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PatientMedication;
use App\Http\Requests\PatientMedicationRequest;
use Carbon\Carbon;
use Illuminate\Http\Response;

class PatientMedicationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\PatientMedicationRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PatientMedicationRequest $request)
    {
        // Verify the user can access this function via policy
        $this->authorize('create', PatientMedication::class);

        $patientMedication = PatientMedication::create([
            ...$request->safe()->except(['start_date', 'end_date']),
            'start_date' => Carbon::parse($request->start_date)->format('Y-m-d'),
            'end_date' => Carbon::parse($request->end_date)->format('Y-m-d'),
        ]);

        return response()->json(
            [
                'message' => 'Patient Medication created',
                'data' => $patientMedication,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePatientMedicationRequest  $request
     * @param  \App\Models\PatientMedication  $patientMedication
     * @return \Illuminate\Http\Response
     */
    public function update(PatientMedicationRequest $request, PatientMedication $patientMedication)
    {
        // Verify the user can access this function via policy
        $this->authorize('update', $patientMedication);

        $data = $request->safe()->except(['patient_id', 'start_date', 'end_date']);

        $patientMedication->update([
            ...$data,
            'start_date' => Carbon::parse($request->start_date)->format('Y-m-d'),
            'end_date' => Carbon::parse($request->end_date)->format('Y-m-d'),
        ]);

        return response()->json(
            [
                'message' => 'Patient Medication updated',
                'data' => $patientMedication,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PatientMedication  $patient_medication
     * @return \Illuminate\Http\Response
     */
    public function destroy(PatientMedication $patientMedication)
    {
        // Verify the user can access this function via policy
        $this->authorize('delete', $patientMedication);

        $patientMedication->delete();

        return response()->json(
            [
                'message' => 'Patient Medication deleted',
            ],
            Response::HTTP_OK
        );
    }
}
