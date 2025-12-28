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
           'الايميل' => 'required|email|',
           'كلمة_السر' => 'required|string|min:8|',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'email' => $this->input('الايميل'),
            'password' => $this->input('كلمة_السر'),
        ]);
    }

    public function messages(): array
    {
        return [
            'الايميل.required'    => 'حقل الايميل مطلوب.',
            'الايميل.email'       => 'حقل الايميل يجب أن يكون عنوان بريد إلكتروني صالح.',
            'كلمة_السر.required'  => 'حقل كلمة السر مطلوب.',
            'كلمة_السر.string'    => 'حقل كلمة السر يجب أن يكون نصاً.',
            'كلمة_السر.min'       => 'حقل كلمة السر يجب أن يكون على الأقل 8 أحرف.',
        ];
    }
}
