<?php

namespace App\Http\Requests\V1\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'email',
                'unique:users,email,' . $this->route('teacher')
            ],
            'password' => ['sometimes', 'nullable', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * أسماء الحقول بالعربي (للداشبورد)
     */
    public function attributes(): array
    {
        return [
            'name' => 'اسم المعلم',
            'email' => 'البريد الإلكتروني',
            'password' => 'كلمة المرور',
        ];
    }

    /**
     * رسائل عربية احترافية
     */
    public function messages(): array
    {
        return [
            'name.string' => 'اسم المعلم يجب أن يكون نصًا',
            'name.max' => 'اسم المعلم يجب ألا يزيد عن 255 حرفًا',

            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة',
            'email.unique' => 'البريد الإلكتروني مستخدم مسبقًا',

            'password.min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
            'password.confirmed' => 'تأكيد كلمة المرور غير مطابق',
        ];
    }
}
