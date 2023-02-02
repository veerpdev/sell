<?php

namespace App\Http\Controllers;

use App\Models\PatientAlert;
use App\Http\Requests\PatientAlertRequest;
use App\Http\Requests\PatientAlertUpdateRequest;
use App\Models\Patient;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PatientAlertController extends Controller
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
     * @param  \App\Http\Requests\StorePatientAlertRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PatientAlertRequest $request, Patient $patient)
    {
        $this->authorize('create', PatientAlert::class);
        PatientAlert::create([
            ...$request->validated(),
            'created_by' => auth()->user()->id
        ]);

        return response()->json(
            [
                'message' => 'Patient alert added',
                'data' => $patient,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePatientAlertRequest  $request
     * @param  \App\Models\PatientAlert  $patientAlert
     * @return \Illuminate\Http\Response
     */
    public function update(PatientAlertUpdateRequest $request, PatientAlert $patientAlert)
    {
        $patientAlert->update($request->validated());

        return response()->json(
            [
                'message' => 'Patient alert updated',
                'data' => $patientAlert,
            ],
            Response::HTTP_OK
        );
    }
}
