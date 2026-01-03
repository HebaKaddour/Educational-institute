<?php

namespace App\Http\Requests\V1\Students;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
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
            // بيانات الطالب
            'full_name' => 'required|string|max:255',
            'identification_number' => 'required|numeric|unique:students,identification_number',
            'student_mobile' => 'nullable|string|max:20',
            'guardian_mobile' => 'nullable|string|max:20',
            'age' => 'required|integer|min:3',
            'gender' => 'required|in:ذكر,انثى',
            'school' => 'required|string|max:255',
            'grade' => 'nullable|string|max:50',

            // الاشتراكات متعددة
            'subscriptions' => 'required|array|min:1',
            'subscriptions.*.subject_id' => 'required|exists:subjects,id',
            'subscriptions.*.start_date' => 'required|date',
            'subscriptions.*.end_date' => 'required|date|after_or_equal:subscriptions.*.start_date',
            'subscriptions.*.fee' => 'required|numeric|min:0',
            'subscriptions.*.discount' => 'nullable|numeric|min:0',
            'subscriptions.*.paid_amount' => 'nullable|numeric|min:0',
        ];
    }
        public function attributes(): array
    {
        return [
            'full_name' => 'الاسم الكامل',
            'identification_number' => 'رقم الهوية',
            'student_number' => 'رقم الطالب',
            'guardian_number' => 'رقم ولي الأمر',
            'age' => 'العمر',
            'gender' => 'الجنس',
            'school' => 'المدرسة',
            'grade' => 'الصف',
            'student_mobile' => 'موبايل الطالب',
            'guardian_mobile' => 'موبايل ولي الأمر',
            'subscriptions.*.subject_id' => 'المادة',
            'subscriptions.*.fee' => 'الرسوم',
            'subscriptions.*.discount' => 'الخصم',
            'subscriptions.*.paid_amount' => 'المبلغ المدفوع',
            'subscriptions.*.start_date' => 'تاريخ البداية',
            'subscriptions.*.end_date' => 'تاريخ النهاية',
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'الاسم الكامل مطلوب',
            'identification.required' => 'رقم الهوية مطلوب',
            'identification_number.unique' => 'رقم الهوية مستخدم مسبقًا',
            'age.required' => 'العمر مطلوب',
            'gender.required' => 'الجنس مطلوب',
            'school.required' => 'المدرسة مطلوبة',
            'subscriptions.required' => 'يجب إضافة اشتراك واحد على الأقل',
            'subscriptions.*.subject_id.exists' => 'المادة المختارة غير موجودة',
            'subscriptions.*.subject_id.required' => 'المادة مطلوبة',
            'subscriptions.*.start_date.required' => 'تاريخ البداية مطلوب',
            'subscriptions.*.end_date.required' => 'تاريخ النهاية مطلوب',
            'subscriptions.*.end_date.after_or_equal' => 'تاريخ النهاية يجب أن يكون بعد أو مساوي لتاريخ البداية',
            'subscriptions.*.fee.required' => 'الرسوم مطلوبة لكل مادة',
        ];
    }
}
