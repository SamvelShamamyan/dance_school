<?php

namespace App\Http\Requests\GroupRequest;

use Illuminate\Foundation\Http\FormRequest;

class GroupStaffStoreRequest extends FormRequest
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
            'group_id'      => 'required|integer|exists:groups,id',
            'add_staff'     => 'required|array|min:1',
            'add_staff.*'   => 'integer|exists:staff,id',
        ];
    }

    public function messages(): array
    {
        return [
            'group_id.required'     => 'Խումբը պարտադիր է:',
            'add_staff.required'    => 'Ընտրեք առնվազն մեկ աշխատակից(պարուսույց):',
        ];
    }
}
