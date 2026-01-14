<?php

namespace App\Http\Requests\V1\Students;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
        // بيانات الطالب
            'full_name' => 'sometimes|string|max:255',
            'identification_number' => 'sometimes|numeric|unique:students,identification_number,' . optional($this->route('subscription')->student)->id,
            'age' => 'sometimes|integer|min:3',
            'gender' => 'sometimes|in:ذكر,انثى',
            'school' => 'sometimes|string|max:255',
            'grade' => 'sometimes|nullable|string|max:50',
            'section' => 'sometimes|string|max:50',
            'status' => 'sometimes|in:نشط,منسحب',
            'student_mobile' => 'sometimes|nullable|string|max:20',
            'guardian_mobile' => 'sometimes|nullable|string|max:20',

            // بيانات الاشتراك
            'subscription.start_date' => 'sometimes|date',
            'subscription.month_number' => 'sometimes|integer|min:1',
            'subscription.monthly_fee' => 'sometimes|numeric|min:0',
            'subscription.discount_percentage' => 'sometimes|numeric|min:0|max:100',
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
            'subscription.start_date' => 'تاريخ بدء الاشتراك',
            'subscription.month_number' => 'عدد الأشهر',
            'subscription.monthly_fee' => 'قيمة الاشتراك الشهري',
            'subscription.discount_percentage' => 'نسبة الخصم',
            'status' => 'الحالة',
        ];
    }

    public function messages(): array
    {
        return [
            'identification_number.unique' => 'رقم الهوية مستخدم مسبقًا',
            'gender.in' => 'الجنس يجب أن يكون ذكر أو أنثى',
            'subscription.discount_percentage.max' => 'نسبة الخصم لا يمكن أن تتجاوز 100',
            'subscription.monthly_fee.min' => 'قيمة الاشتراك الشهري يجب أن تكون أكبر من صفر',
            'subscription.month_number.min' => 'عدد الأشهر يجب أن يكون أكبر من صفر',
            'status.in' => 'الحالة يجب أن تكون نشط أو منسحب',

        ];
    }
}
