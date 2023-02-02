<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PatientAllergy;
use App\Http\Requests\PatientAllergyRequest;
use Illuminate\Http\Response;

class PatientAllergyController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PatientAllergyRequest $request)
    {
        // Verify the user can access this function via policy
        $this->authorize('create', PatientAllergy::class);

        $patientAllergies = PatientAllergy::create([
            ...$request->validated()
        ]);

        return response()->json(
            [
                'message' => 'Patient Allergy created',
                'data' => $patientAllergies,
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
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(
        PatientAllergyRequest $request,
        PatientAllergy $patientAllergy
    ) {
        // Verify the user can access this function via policy
        $this->authorize('update', $patientAllergy);

        $data = $request->safe()->except(['patient_id']);

        $patientAllergy->update($data);

        return response()->json(
            [
                'message' => 'Patient Allergy updated',
                'data' => $patientAllergy,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(PatientAllergy $patientAllergy)
    {
        // Verify the user can access this function via policy
        $this->authorize('delete', $patientAllergy);

        $patientAllergy->delete();

        return response()->json(
            [
                'message' => 'Patient Allergy deleted',
                'data' => $patientAllergy,
            ],
            Response::HTTP_OK
        );
    }
}
