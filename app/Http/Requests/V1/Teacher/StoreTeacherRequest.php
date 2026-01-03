<?php

namespace App\Http\Requests\V1\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    public function attributes() : array
    {
        return [
            'name'     => 'اسم المعلم',
            'email'    => 'البريد الإلكتروني',
            'password' => 'كلمة المرور',
            'password_confirmation' => 'تأكيد كلمة المرور',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'اسم المعلم مطلوب',
            'email.required'    => 'البريد الإلكتروني مطلوب',
            'email.email'       => 'صيغة البريد الإلكتروني غير صحيحة',
            'email.unique'      => 'البريد الإلكتروني مستخدم مسبقاً',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.min'      => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
            'password.confirmed'=> 'تأكيد كلمة المرور غير مطابق',
        ];
    }
}
