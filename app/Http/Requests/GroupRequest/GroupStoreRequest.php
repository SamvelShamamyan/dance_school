<?php

namespace App\Http\Requests\GroupRequest;

use Illuminate\Foundation\Http\FormRequest;

class GroupStoreRequest extends FormRequest
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
            'group_name'    => 'required|string|max:25',
            'group_date'    => [
                'required',
                'date_format:d.m.Y',
                // 'after_or_equal:today'
            ],
        ];
    }

    public function messages(): array
    {
       return [
        'group_name.required'       => 'Խմբի անունը պարտադիր է:',
        'group_name.string'         => 'Խմբի անունը պետք է լինի տեքստային:',
        'group_name.max'            => 'Խմբի անունը չի կարող լինել ավելի քան 25 նիշ:',
        'group_date.required'       => 'Ամսաթիվը պարտադիր է:',
        'group_date.date_format'    => 'Ամսաթվի ձևաչափը պետք է լինի օր.ամիս.տարի (օրինակ՝ 06.08.2025):',
        // 'group_date.after_or_equal' => 'Ամսաթիվը չի կարող լինել անցյալում:',
    ];
    }
}
