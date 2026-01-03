<?php

namespace App\Http\Requests\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

class AuthRequest extends FormRequest
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
           'email' => 'required|email|',
           'password' => 'required|string|min:8|',
        ];
    }

    public function attributes() : array
    {
        return [
            'email' => 'الايميل',
            'password' => 'كلمة المرور',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'    => 'حقل الايميل مطلوب.',
            'email.email'       => 'حقل الايميل يجب أن يكون عنوان بريد إلكتروني صالح.',
            'password.required'  => 'حقل كلمة السر مطلوب.',
            'password.string'    => 'حقل كلمة السر يجب أن يكون نصاً.',
            'password.min'       => 'حقل كلمة السر يجب أن يكون على الأقل 8 أحرف.',
        ];
    }
}
