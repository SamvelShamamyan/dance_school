<?php

namespace App\Http\Requests\StudentRequest;

use Illuminate\Foundation\Http\FormRequest;

class StudentStoreRequest extends FormRequest
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
            'first_name'  => 'required|string|max:25',
            'last_name'   => 'required|string|max:25',
            'father_name' => 'required|string|max:25',
            'address'     => 'required|string|max:25',
            'soc_number'  => 'required|string|max:25',
            'email'       => 'required|email|unique:students,email',
            'birth_date' => [
                'required',
                'date_format:d.m.Y',
                // 'after_or_equal:today'
            ],
            'student_date' => [
                'required',
                'date_format:d.m.Y',
                // 'after_or_equal:today'
            ],
        ];
    }

    public function messages(): array
    {
      return [
            'first_name.required'  => 'Անունը պարտադիր է:',
            'first_name.string'    => 'Անունը պետք է լինի տեքստային:',
            'first_name.max'       => 'Անունը չի կարող գերազանցել 25 նիշը:',

            'last_name.required'   => 'Ազգանունը պարտադիր է:',
            'last_name.string'     => 'Ազգանունը պետք է լինի տեքստային:',
            'last_name.max'        => 'Ազգանունը չի կարող գերազանցել 25 նիշը:',

            'father_name.required' => 'Հայրանունը պարտադիր է:',
            'father_name.string'   => 'Հայրանունը պետք է լինի տեքստային:',
            'father_name.max'      => 'Հայրանունը չի կարող գերազանցել 25 նիշը:',

            'address.required'     => 'Բնակության հասցեն պարտադիր է:',
            'address.string'       => 'Բնակության հասցեն պետք է լինի տեքստային:',
            'address.max'          => 'Բնակության հասցեն չի կարող գերազանցել 100 նիշը:',

            'soc_number.required'  => 'ՀԾՀ-ն պարտադիր է:',
            'soc_number.string'    => 'ՀԾՀ-ն համարն պետք է լինի տեքստային:',
            'soc_number.max'       => 'ՀԾՀ-ն համարն չի կարող գերազանցել 25 նիշը:',

            'email.required'       => 'Էլ. փոստը պարտադիր է:',
            'email.email'          => 'Էլ. փոստի ձևաչափը սխալ է:',
            'email.unique'         => 'Այս էլ. փոստը արդեն օգտագործվում է:',

            'birth_date.required'     => 'Ծննդյան ամսաթիվը պարտադիր է:',
            'birth_date.date_format'  => 'Ծննդյան ամսաթվի ձևաչափը պետք է լինի օր.ամիս.տարի (օրինակ՝ 06.08.2025):',
            // 'birth_date.before_or_equal' => 'Ծննդյան ամսաթիվը չի կարող լինել ապագայում:',

            'student_date.required'     => 'Ուսուցման ընդունվելու ամսաթիվը պարտադիր է:',
            'student_date.date_format'  => 'Ուսուցման ամսաթվի ձևաչափը պետք է լինի օր.ամիս.տարի (օրինակ՝ 06.08.2025):',
            // 'student_date.after_or_equal' => 'Ուսուցման ամսաթիվը չի կարող լինել մինչև ծննդյան ամսաթիվը:',
        ];
    }
}
