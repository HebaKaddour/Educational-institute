<?php

namespace App\Http\Requests\V1\Students;

use Illuminate\Foundation\Http\FormRequest;

class AddSubscriptionRequest extends FormRequest
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
            'subject_id' => ['required', 'exists:subjects,id'],
            'fee' => ['required', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['required', 'numeric', 'min:0'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ];
    }

    public function attributes(): array
    {
        return [
            'subject_id' => 'المادة',
            'fee' => 'الرسوم',
            'discount' => 'الخصم',
            'paid_amount' => 'المبلغ المدفوع',
            'start_date' => 'تاريخ البداية',
            'end_date' => 'تاريخ النهاية',
        ];
    }

     public function messages(): array
    {
        return [
            'full_name.required' => 'الاسم الكامل مطلوب',
            'id_number.required' => 'رقم الهوية مطلوب',
            'identification_number.unique' => 'رقم الهوية مستخدم مسبقًا',
            'age.required' => 'العمر مطلوب',
            'gender.required' => 'الجنس مطلوب',
            'school.required' => 'المدرسة مطلوبة',
            'subscriptions.required' => 'يجب إضافة اشتراك واحد على الأقل',
            'subject_id.exists' => 'المادة المختارة غير موجودة',
            'subject_id.required' => 'المادة مطلوبة',
            'start_date.required' => 'تاريخ البداية مطلوب',
            'end_date.required' => 'تاريخ النهاية مطلوب',
            'end_date.after_or_equal' => 'تاريخ النهاية يجب أن يكون بعد أو مساوي لتاريخ البداية',
            'fee.required' => 'الرسوم مطلوبة لكل مادة',
            'paid_amount.required' => 'المبلغ المدفوع مطلوب'
        ];
    }
}
