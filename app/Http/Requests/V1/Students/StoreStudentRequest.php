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
             // student
            'full_name' => 'required|string|max:255',
            'identification_number' => 'required|numeric|unique:students,identification_number',
            'age' => 'required|integer|min:3',
            'gender' => 'required|in:ذكر,انثى',
            'school' => 'required|string|max:255',
            'grade' => 'nullable|string|max:50',
            'section' => 'required|string|max:50',
            'student_mobile' => 'required|string|max:20',
            'guardian_mobile' => 'required|string|max:20',

            // subscription
            'subscription.month_number' => 'required|integer|min:1',
            'subscription.start_date' => 'required|date',
            'subscription.monthly_fee' => 'required|numeric|min:0',
            'subscription.discount_percentage' => 'nullable|numeric|min:0|max:100',
        ];
    }


    public function validatedSubscription(): array
    {
        $subscription = $this->input('subscription', []);
        $subscription['discount_percentage'] = $subscription['discount_percentage'] ?? 0;
        return $subscription;
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
            'month_number' => 'عدد الأشهر',
            'monthly_fee' => 'الرسوم الشهرية',
            'discount' => 'الخصم',
            'section' => 'الشعية الدراسية',
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'الاسم الكامل مطلوب',
            'identification_number.required' => 'رقم الهوية مطلوب',
            'identification_number.unique' => 'رقم الهوية مستخدم مسبقًا',
            'age.required' => 'العمر مطلوب',
            'gender.required' => 'الجنس مطلوب',
            'school.required' => 'المدرسة مطلوبة',
            'subscription.month_number.required' => 'عدد الأشهر مطلوب',
            'student_mobile.required' => 'موبايل الطالب مطلوب',
            'guardian_mobile.required' => 'موبايل ولي الأمر مطلوب',
            'subscription.start_date.required' => 'تاريخ البداية مطلوب',
            'subscription.start_date.date' => 'تاريخ البداية يجب أن يكون تاريخًا صحيحًا',
            'subscription.monthly_fee.required' => 'الرسوم مطلوبة  ',
            'subscription.discount_percentage.min' => 'الخصم لا يمكن أن يكون أقل من 0',
            'subscription.discount_percentage.max' => 'الخصم لا يمكن أن يكون أكثر من 100',
            'section.required' => 'الشعبة الدراسية مطلوب',
        ];
    }
}
