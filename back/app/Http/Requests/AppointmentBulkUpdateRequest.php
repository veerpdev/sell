<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AppointmentBulkUpdateRequest extends FormRequest
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
            'id' => 'nullable|integer',
            'appointments' => 'required|array',
            'appointments*arrival_time' => 'required',
            'appointments*start_time' => 'required',
            'appointments*end_time' => 'required',
            'appointments*clinic_id' => 'required',
            'appointments*specialist_id' => 'required',
        ];
    }
}
