<?php

namespace App\Http\Requests\GroupRequest;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Support\Facades\Auth;

class GroupStudentStoreRequest extends FormRequest
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
            'group_id'        => 'required|integer|exists:groups,id',
            'add_student'     => 'required|array|min:1',
            'add_student.*'   => 'integer|exists:students,id',
            'school_id'       => Auth::user()->hasRole('super-admin')
                ? 'required|integer|exists:school_names,id'
                : 'nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'group_id.required'     => 'Խումբը պարտադիր է:',
            'add_student.required'  => 'Ընտրեք առնվազն մեկ աշակերտ:',
            'school_id.required'    => 'Ուս․ հաստատություն պարտադիր է:',
            'school_id.integer'     => 'Ուս․ հաստատություն ID-ն պետք է լինի ամբողջ թիվ:',
            'school_id.exists'      => 'Նշված ուս․ հաստատություն ID-ն գոյություն չունի:',
        ];
    }
}
