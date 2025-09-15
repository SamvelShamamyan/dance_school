<?php

namespace App\Http\Requests\RoomRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class RoomUpdateRequest extends FormRequest
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
             'school_id' => Auth::user()->hasRole('super-admin')
                ? 'required|integer|exists:school_names,id'
                : 'nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Անվանումը պարտադիր է:',
            'name.max' => 'Անվանումը չպետք է գերազանցի 25 նիշը:',
            'school_id.required' => 'Ուս․ հաստատություն պարտադիր է:',
            'school_id.integer' => 'Ուս․ հաստատություն ID-ն պետք է լինի ամբողջ թիվ:',
            'school_id.exists' => 'Նշված ուս․ հաստատություն ID-ն գոյություն չունի:',
        ];
    }
}
