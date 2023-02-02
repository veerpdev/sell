<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**

 */
class OutgoingMessageLogIndexRequest extends FormRequest
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
            'document_id'           => 'nullable|int',
            'sending_user'          => 'nullable|int',
            'sending_doctor_user'   => 'nullable|int',
            'send_method'           => 'nullable|string',
            'send_status'           => 'nullable|string',
        ];
    }
}
