<?php

namespace App\Http\Requests\GroupRequest;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;


class StudentRepeatStoreRequest extends FormRequest
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
            'student_id' => [
                'bail', 'required', 'integer',
                Rule::exists('students', 'id'),
            ],
            'group_id' => ['bail', 'integer', Rule::exists('groups', 'id')],
        ];
    }

    public function messages(): array
    {
       return [
            'group_id.required' => 'Խումբը պարտադիր է:',
            'group_id.integer'  => 'Խումբը պետք է լինի թվային նույնացուցիչ:',
            'group_id.exists'   => 'Ընտրված խումբ չի գտնվել:',

            'student_id.required' => 'Աշակերտը պարտադիր է:',
            'student_id.integer'  => 'Աշակերտը պետք է լինի թվային նույնացուցիչ:',
            'student_id.exists'   => 'Ընտրված Աշակերտ չի գտնվել:',
    ];
    }
}
