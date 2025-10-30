<?php

namespace App\Http\Requests\StudentRequest;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Support\Facades\Auth;


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
            'first_name' => ['required','string','max:25'],
            'last_name' => 'required|string|max:25',
            'father_name' => 'required|string|max:25',
            // 'address' => 'required|string|max:25',
            'address' => 'nullable|string|max:25',
            'soc_number' => 'nullable|string|max:25',
            'email' => 'required|email|unique:students,email',
            'phone_1' => 'required|digits:9',
            'phone_2' => 'nullable|digits:9',
            'parent_first_name' => ['nullable','string','max:25'],
            'parent_last_name' => ['nullable','string','max:25'],
            'student_expected_payments' => 'required|numeric|min:0',
            'birth_date' => [
                'required',
                'date_format:d.m.Y',
                // 'after_or_equal:today'
            ],
            'created_date' => [
                'required',
                'date_format:d.m.Y',
                // 'after_or_equal:today'
            ],
            'files.*' => ['nullable','file','mimes:jpg,jpeg,png,pdf','max:10240'], // 10 МБ
             'school_id' => Auth::user()->hasRole('super-admin')
                ? 'required|integer|exists:school_names,id'
                : 'nullable',

            // 'group_id' => Auth::user()->hasRole('super-admin')
            //     ? 'required|integer|exists:groups,id'
            //     : 'nullable',      
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

            // 'address.required'     => 'Բնակության հասցեն պարտադիր է:',
            'address.string'       => 'Բնակության հասցեն պետք է լինի տեքստային:',
            'address.max'          => 'Բնակության հասցեն չի կարող գերազանցել 25 նիշը:',

            'soc_number.string'    => 'ՀԾՀ-ն համարն պետք է լինի տեքստային:',
            'soc_number.max'       => 'ՀԾՀ-ն համարն չի կարող գերազանցել 25 նիշը:',

            'email.required'       => 'Էլ. փոստը պարտադիր է:',
            'email.email'          => 'Էլ. փոստի ձևաչափը սխալ է:',
            'email.unique'         => 'Այս էլ. փոստը արդեն օգտագործվում է:',

            'phone_1.required' => 'Հեռախոսահամարը պարտադիր է:',
            'phone_1.digits' => 'Հեռախոսահամարը պետք է լինի 9 թվանշան:',

            'phone_2.digits' => 'Հեռախոսահամարը պետք է լինի 9 թվանշան:',

            'student_expected_payments.required'       => 'Գումարը պարտադիր է:',
            'student_expected_payments.numeric'        => 'Գումարը պետք է լինի թիվ:',
            'student_expected_payments.min'            => 'Գումարը չի կարող լինել բացասական:',

            'birth_date.required'     => 'Ծննդյան ամսաթիվը պարտադիր է:',
            'birth_date.date_format'  => 'Ծննդյան ամսաթվի ձևաչափը պետք է լինի օր.ամիս.տարի (օրինակ՝ 06.08.2025):',
            // 'birth_date.before_or_equal' => 'Ծննդյան ամսաթիվը չի կարող լինել ապագայում:',

            'created_date.required'     => 'Ուսուցման ընդունվելու ամսաթիվը պարտադիր է:',
            'created_date.date_format'  => 'Ուսուցման ամսաթվի ձևաչափը պետք է լինի օր.ամիս.տարի (օրինակ՝ 06.08.2025):',
            // 'created_date.after_or_equal' => 'Ուսուցման ամսաթիվը չի կարող լինել մինչև ծննդյան ամսաթիվը:',

            'school_id.required'        => 'Ուս․ հաստատություն պարտադիր է:',
            'school_id.integer'         => 'Ուս․ հաստատություն ID-ն պետք է լինի ամբողջ թիվ:',
            'school_id.exists'          => 'Նշված ուս․ հաստատություն ID-ն գոյություն չունի:',

            // 'group_id.required'        => 'Խումբը պարտադիր է:',
            // 'group_id.integer'         => 'Խումբ ID-ն պետք է լինի ամբողջ թիվ:',
            // 'group_id.exists'          => 'Նշված խումբ ID-ն գոյություն չունի:',
        ];
    }
}
