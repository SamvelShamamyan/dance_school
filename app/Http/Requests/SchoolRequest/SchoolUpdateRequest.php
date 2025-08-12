<?php

namespace App\Http\Requests\SchoolRequest;

use Illuminate\Foundation\Http\FormRequest;

class SchoolUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:25',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Անվանումը պարտադիր է:',
            'name.max' => 'Անվանումը չպետք է գերազանցի 25 նիշը:',
        ];
    }
}
