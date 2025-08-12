<?php

namespace App\Http\Requests\PaymentRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => [
                'bail', 'required', 'integer',
                Rule::exists('students', 'id'),
            ],

            'group_id' => ['bail', 'integer', Rule::exists('groups', 'id')],

            'paid_at' => ['bail', 'required', 'date_format:d.m.Y'],

            'amount' => ['bail', 'required', 'numeric', 'min:0'],

            'method' => ['bail', 'required', Rule::in(['cash', 'card'])],

            'status' => ['bail', 'required', Rule::in(['paid', 'pending'])],

            'comment' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [

            'group_id.required' => 'Խումբը պարտադիր է:',
            'group_id.integer'  => 'Խումբը պետք է լինի թվային նույնացուցիչ:',
            'group_id.exists'   => 'Ընտրված խումբ չի գտնվել:',

            'student_id.required' => 'Սանը պարտադիր է:',
            'student_id.integer'  => 'Սանը պետք է լինի թվային նույնացուցիչ:',
            'student_id.exists'   => 'Ընտրված սան չի գտնվել:',

            'paid_at.required'    => 'Վճարման ամսաթիվը պարտադիր է:',
            'paid_at.date_format' => 'Ամսաթվի ձևաչափը պետք է լինի օր.ամիս.տարի (օր.` 06.08.2025):',

            'amount.required' => 'Գումարը պարտադիր է:',
            'amount.numeric'  => 'Գումարը պետք է լինի թիվ:',
            'amount.min'      => 'Գումարը չի կարող լինել բացասական:',

            'method.required' => 'Վճարման տարբերակը պարտադիր է:',
            'method.in'       => 'Վճարման տարբերակը պետք է լինի՝ կանխիկ կամ անկանխիկ:',

            'status.required' => 'Կարգավիճակը պարտադիր է:',
            'status.in'       => 'Կարգավիճակը պետք է լինի՝ վճարված կամ սպասման մեջ:',

            'comment.string'  => 'Մեկնաբանությունը պետք է լինի տեքստային:',
            'comment.max'     => 'Մեկնաբանությունը չի կարող գերազանցել 255 նիշը:',
        ];
    }

}
