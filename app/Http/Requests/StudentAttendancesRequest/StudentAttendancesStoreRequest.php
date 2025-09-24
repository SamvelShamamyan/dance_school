<?php

namespace App\Http\Requests\StudentAttendancesRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class StudentAttendancesStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        $ids = $this->input('students', []); 

        return [
            'schedule_group_id'   => ['nullable','integer'],
            'inspection_date'     => ['required','date_format:d.m.Y'],
            'students'            => ['required','array','min:1'],

            'attendance_check'    => ['required','array', 'required_array_keys:'.implode(',', $ids)],
            'attendance_check.*'  => ['in:0,1'],
            'attendance_guest'    => ['nullable','array'],
            'attendance_guest.*'  => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'inspection_date.required' => 'Ընտրեք ստուգման ամսաթիվը:',
            'inspection_date.date_format' => 'Ստուգման ամսաթվի ձևաչափը պետք է լինի օր.ամիս.տարի (օրինակ՝ 06.08.2025)',
            'students.required'        => 'Աշակերտների ցուցակը բացակայում է:',
            'students.min'             => 'Չկան աշակերտներ ցուցակում:',
            'attendance_check.required'=> 'Ավելացրեք առնվազն մեկ ուսանող:',
            'attendance_check.array'   => 'Տվյալները սխալ են:',
            'attendance_check.required_array_keys' => 'Յուրաքանչյուր աշակերտի համար ընտրեք Ներկա/Բացակա:',
        ];
    }
}
