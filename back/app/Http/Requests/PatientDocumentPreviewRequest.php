<?php

namespace App\Http\Requests;

use App\Enum\DocumentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class PatientDocumentPreviewRequest extends FormRequest
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
            'title'                 => 'required|string',
            'report_data'           => 'required|array',
            'doctor_address_book'   => 'required|string',
            'appointment_id'        => 'required|numeric|exists:appointments,id',
            'specialist_id'         => 'required|numeric|exists:users,id',
            'document_name'         => 'required|string',
            'header_footer_id'      => 'nullable|numeric',
            'procedures_undertaken' => 'sometimes|array',
            'extra_items_used'      => 'sometimes|array',
            'admin_items_used'      => 'sometimes|array',
            'icd_10_code'           => 'sometimes|array',
            'patient_demographic'   => 'required|boolean',
            'current_medications'   => 'required|boolean',
            'patient_allergies'     => 'required|boolean',
            'past_medical_history'  => 'required|boolean',
            'document_type'         => ['required', new Enum(DocumentType::class)],
        ];
    }

    /**
     * Get the description of body parameters.
     *
     * @return array<string, array>
     */
    public function bodyParameters()
    {
        return [
            // 'patient_id' => [
            //     'description' => '',
            //     'example'     => '',
            // ],
            // 'body' => [
            //     'description' => '',
            //     'example'     => '',
            // ],
        ];
    }
}
