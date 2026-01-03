<?php

namespace App\Http\Requests\V1\Students;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'full_name' => ['sometimes', 'string', 'max:255'],
            'age' => ['sometimes', 'integer', 'min:3', 'max:100'],
            'identification_number' => [
                'sometimes',
                'numeric',
                'unique:students,identification_number,' . $this->route('student')->id
            ],
            'gender' => ['sometimes', 'in:ذكر,انثى'],
            'school' => ['sometimes', 'string', 'max:255'],
            'grade' => ['sometimes', 'string', 'max:255'],
            'student_mobile' => ['sometimes', 'string', 'max:20'],
            'guardian_mobile' => ['sometimes', 'string', 'max:20'],
        ];
    }

    public function attributes(): array
    {
        return [
            'full_name' => 'اسم الطالب',
            'age' => 'العمر',
            'identification_number' => 'رقم الهوية',
            'gender' => 'الجنس',
            'school' => 'المدرسة',
            'grade' => 'الصف',
            'student_mobile' => 'موبايل الطالب',
            'guardian_mobile' => 'موبايل ولي الأمر',
        ];
    }

    public function messages(): array
    {
        return [
            'identification_number.unique' => 'رقم الهوية مستخدم مسبقًا',
            'gender.in' => 'الجنس يجب أن يكون ذكر أو أنثى',
        ];
    }
}
