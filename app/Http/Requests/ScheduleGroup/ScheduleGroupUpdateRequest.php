<?php

namespace App\Http\Requests\ScheduleGroup;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;


class ScheduleGroupUpdateRequest extends FormRequest
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
            'day'        => ['required', 'integer', 'between:1,7'],
            'start'      => ['required', 'date_format:H:i'],
            'end'        => ['required', 'date_format:H:i', 'after:start'],

            'school_id' => Auth::user()->hasRole('super-admin')
                ? ['required', 'integer', Rule::exists('school_names', 'id')]
                : 'nullable',
            'group_id'   => ['required', 'integer', Rule::exists('groups', 'id')],
            'room_id'    => ['required', 'integer', Rule::exists('rooms', 'id')],

            'title'      => ['nullable', 'string', 'max:255'],
            'note'       => ['nullable', 'string', 'max:255'],
            'color'      => ['nullable', 'string', 'max:32', Rule::in(['blue','green','purple','orange'])],
        ];
    }

    public function messages(): array
    {
        return [
            'day.required'   => 'Պարտադիր է ընտրել շաբաթվա օրը։',
            'day.integer'    => 'Օրը պետք է լինի թիվ։',
            'day.between'    => 'Օրը պետք է լինի 1-ից 7 միջակայքում։',

            'start.required'     => 'Նշեք դասի սկիզբը։',
            'start.date_format'  => 'Սկիզբը պետք է լինի ժամի ձևաչափով (օր.` 09:30)։',

            'end.required'     => 'Նշեք դասի ավարտը։',
            'end.date_format'  => 'Ավարտը պետք է լինի ժամի ձևաչափով (օր.` 10:30)։',
            'end.after'        => 'Ավարտի ժամը պետք է ավելի ուշ լինի, քան սկիզբը։',

            'school_id.required' => 'Պարտադիր է ընտրել դպրոց։',
            'school_id.integer'  => 'Դպրոցի ID-ն պետք է լինի թիվ։',
            'school_id.exists'   => 'Ընտրված դպրոցը գոյություն չունի։',

            'group_id.required' => 'Պարտադիր է ընտրել խումբ։',
            'group_id.integer'  => 'Խմբի ID-ն պետք է լինի թիվ։',
            'group_id.exists'   => 'Ընտրված խումբը գոյություն չունի։',

            'room_id.required' => 'Պարտադիր է ընտրել դահլիճ։',
            'room_id.integer'  => 'Դահլիճի ID-ն պետք է լինի թիվ։',
            'room_id.exists'   => 'Ընտրված դահլիճը գոյություն չունի։',

            'title.string'  => 'Վերնագիրը պետք է լինի տեքստ։',
            'title.max'     => 'Վերնագիրը չի կարող լինել ավելի քան 255 նիշ։',

            'note.string'   => 'Նշումը պետք է լինի տեքստ։',
            'note.max'      => 'Նշումը չի կարող լինել ավելի քան 255 նիշ։',

            'color.string'  => 'Գույնը պետք է լինի տեքստ։',
            'color.max'     => 'Գույնի դաշտը չի կարող լինել ավելի քան 32 նիշ։',
            'color.in'      => 'Գույնը պետք է լինի հետևյալներից մեկը՝ blue, green, purple կամ orange։',
        ];
    }
}
