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


        public function attributes(): array
    {
        return [
            'current_password' => 'كلمة السر الحالية',
            'new_password' => 'كلمة السر الجديدة',
            'new_password_confirmation' => 'تأكيد كلمة السر الجديدة',
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'كلمة المرور الحالية مطلوبة',
            'current_password.string' => 'كلمة المرور الحالية يجب أن تكون نصية',
            'new_password.required' => 'يجب ادخال  كلمة مرور جديدة',
            'new_password.string' => 'كلمة المرور الجديدة يجب أن تكون نصية',
            'new_password.min' => 'كلمة المرور الجديدة يجب أن تكون 8 أحرف على الأقل',
            'new_password.confirmed' => 'تأكيد كلمة المرور الجديدة غير مطابق',
        ];
    }
}
