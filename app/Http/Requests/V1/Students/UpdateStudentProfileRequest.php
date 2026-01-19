<?php

namespace App\Http\Requests\V1\Students;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
         return auth()->check() && auth()->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
           'full_name' => 'sometimes|string|max:255',
            'age' => 'sometimes|integer|min:1',
            'identification_number' => 'sometimes|numeric|unique:students,identification_number,' . $this->student->id,
            'gender' => 'sometimes|in:ذكر,انثى',
            'school' => 'sometimes|string|max:255',
            'grade' => 'sometimes|string|max:50',
            'student_mobile' => 'sometimes|string|max:20',
            'guardian_mobile' => 'sometimes|string|max:20',
            'section' => 'sometimes|string|max:50',
        ];
    }

    public function attribute(){
        return [
            'full_name' => 'الاسم الكامل',
            'age' => 'العمر',
            'identification_number' => 'رقم الهوية',
            'sudent_mobile' => 'جوال الطالب',
            'guardian_mobile' => 'جوال ولي الامر',
            'grade' => 'الصف',
            'school' => 'المدرسة',
            'gender' => 'الجنس',
            'section' => 'الشعبة',
        ];
    }

    public function messages(){
        return [
            'full_name.string' => 'يجب أن يكون الاسم الكامل نصًا.',
            'full_name.max' => 'يجب ألا يتجاوز الاسم الكامل 255 حرفًا.',
            'age.integer' => 'يجب أن يكون العمر رقمًا صحيحًا.',
            'age.min' => 'يجب أن يكون العمر على الأقل 1.',
            'identification_number.numeric' => 'يجب أن يكون رقم الهوية رقمًا.',
            'identification_number.unique' => 'رقم الهوية مستخدم بالفعل.',
        ];
}
}
