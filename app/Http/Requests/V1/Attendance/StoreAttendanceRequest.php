<?php

namespace App\Http\Requests\V1\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('create_attendance');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_id' => ['required', 'exists:students,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'week' => ['required', 'integer', 'min:1', 'max:36'],
            'day' => ['required', 'in:السبت,الأحد,الاثنين,الثلاثاء,الأربعاء,الخميس'],
            'status' => ['required', 'in:غياب,حضور'],
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
            'student_id.required' => 'الطالب مطلوب',
            'student_id.exists' => 'الطالب المختار غير موجود',
            'subject_id.required' => 'المادة مطلوبة',
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
