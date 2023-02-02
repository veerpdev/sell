<?php

namespace App\Http\Requests;

use App\Enum\DocumentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
* @bodyParam title      string     required  The name of the document template                  Example: My Document
* @bodyParam sections   string[]   required  An array of sections to appear on the template
*/
class DocumentTemplateRequest extends FormRequest
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
            'title'        => 'required|string',
            'type'         => ['required', new Enum(DocumentType::class)],
            'sections'     => 'required|array',
        ];
    }
}
