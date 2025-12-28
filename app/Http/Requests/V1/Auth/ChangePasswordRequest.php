<?php

namespace App\Http\Requests\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'current_password' => $this->input('كلمة_السر_الحالية'),
            'new_password' => $this->input('كلمة_السر_الجديدة'),
            'new_password_confirmation' => $this->input('تأكيد_كلمة_السر_الجديدة'),
        ]);
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'كلمة المرور الحالية مطلوبة',
            'current_password.string' => 'كلمة المرور الحالية يجب أن تكون نصية',
            'new_password.required' => 'كلمة المرور الجديدة مطلوبة',
            'new_password.string' => 'كلمة المرور الجديدة يجب أن تكون نصية',
            'new_password.min' => 'كلمة المرور الجديدة يجب أن تكون 8 أحرف على الأقل',
            'new_password.confirmed' => 'تأكيد كلمة المرور الجديدة غير مطابق',
        ];
    }
}
