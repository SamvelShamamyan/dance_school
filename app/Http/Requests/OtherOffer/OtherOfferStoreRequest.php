<?php

namespace App\Http\Requests\OtherOffer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;


class OtherOfferStoreRequest extends FormRequest
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
            'school_id' => Auth::user()->hasRole('super-admin')
                ? 'required|integer|exists:school_names,id'
                : 'nullable',
            'group_ids'     => 'required|array|min:1',
            'group_ids.*'   => 'integer|exists:groups,id',
            'name'          => 'required|string|max:25',
            'payments'     => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [

           
            'school_id.required'    => 'Ուս․ հաստատություն պարտադիր է:',
            'school_id.integer'     => 'Ուս․ հաստատություն ID-ն պետք է լինի ամբողջ թիվ:',
            'school_id.exists'      => 'Նշված ուս․ հաստատություն ID-ն գոյություն չունի:',

            'group_ids.required'    => 'Խմբերի ընտրությունը պարտադիր է։',
            'group_ids.array'       => 'Խմբերի տվյալները սխալ ձևաչափով են։',
            'group_ids.min'         => 'Պետք է ընտրել առնվազն մեկ խումբ։',

            'group_ids.*.integer'   => 'Խմբի ID-ն պետք է լինի թիվ։',
            'group_ids.*.exists'    => 'Ընտրված խմբերից մեկը գոյություն չունի։',

            'name.required'         => 'Անվանումը պարտադիր է:',
            'name.max'              => 'Անվանումը չպետք է գերազանցի 25 նիշը:',

            'payments.required'     => 'Գումարը պարտադիր է:',
            'payments.numeric'      => 'Գումարը պետք է լինի թիվ:',
            'payments.min'          => 'Գումարը չի կարող լինել բացասական:',

        ];
    }
}
