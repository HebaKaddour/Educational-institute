<?php

namespace App\Http\Requests\V1\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('update_attendance');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'date' => 'sometimes|date',
                'week' => 'sometimes|integer|min:1|max:36',
                'day'  => 'sometimes|string',
                'subject_id' => 'sometimes|exists:subjects,id',
                'status' => 'sometimes|in:حضور,غياب,بعذر',
                'participation' => 'nullable|boolean',
             ];
    }
    public function attributes(): array
    {
        return [
            'student_id' => 'الطالب',
            'subject_id' => 'المادة',
            'week' => 'الأسبوع',
            'day' => 'اليوم',
            'status' => 'الحالة',
        ];
    }
    public function messages(): array
    {
        return [
            'subject_id.exists' => 'المادة المختارة غير موجودة',
            'week.required' => 'رقم الأسبوع مطلوب',
            'week.integer' => 'رقم الأسبوع يجب أن يكون عددًا صحيحًا',
            'week.min' => 'رقم الأسبوع يجب أن يكون على الأقل 1',
            'week.max' => 'النظام مؤلف من 36 اسبوع فقط',
            'day.required' => 'اليوم مطلوب',
            'day.in' => 'اليوم المختار غير صالح',
            'status.required' => 'الحالة مطلوبة',
            'status.in' => 'الحالة المختارة غير صالحة',
        ];
    }
}
