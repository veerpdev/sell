<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PatientMedicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'patient_id' => 'required|numeric|exists:patients,id',
            'name' => 'required|string',
            'instructions' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ];
    }
}
