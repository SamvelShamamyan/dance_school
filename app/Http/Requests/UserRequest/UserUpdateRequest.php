<?php

namespace App\Http\Requests\UserRequest;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;


class UserUpdateRequest extends FormRequest
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
            'first_name'    => 'required|string|max:25',
            'last_name'     => 'required|string|max:25',
            'father_name'   => 'required|string|max:25',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($this->route('id'))
            ],
            'school_id'     => 'required|exists:school_names,id',
            'role_name'     => 'required|exists:roles,name',
        ];
    }

    public function messages(): array
    {
       return [
            'first_name.required'  => 'Անունը պարտադիր է:',
            'last_name.required'   => 'Ազգանունը պարտադիր է:',
            'father_name.required' => 'Հայրանունը պարտադիր է:',
            'email.required'       => 'Էլ. փոստը պարտադիր է:',
            'email.email'          => 'Էլ. փոստի ձևաչափը սխալ է:',
            'email.unique'         => 'Այս էլ. փոստը արդեն օգտագործվում է:',
            'school_id.required'   => 'Դպրոցը պարտադիր է:',
            'school_id.exists'     => 'Ընտրված դպրոցը գոյություն չունի:',
            'role_name.required'   => 'Դորը պարտադիր է:',
            'role_name.exists'     => 'Ընտրված դերը գոյություն չունի:',
        ];
    }
}
