<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Խնդրում ենք մուտքագրել էլ. փոստի հասցեն։',
            'email.email' => 'Խնդրում ենք մուտքագրել վավեր էլ. փոստի հասցե։',
            'password.required' => 'Գաղտնաբառի լրացումը պարտադիր է։',
        ];
    }
}
