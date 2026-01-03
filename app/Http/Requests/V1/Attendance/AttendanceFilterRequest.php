<?php

namespace App\Http\Requests\V1\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceFilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');   }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_id' => 'sometimes|exists:students,id',
            'subject_id' => 'sometimes|exists:subjects,id',
            'week' => 'sometimes|integer|min:1|max:36',
            'status' => 'sometimes|in:غياب,حضور,excused',
        ];
    }

    public function attributes()
    {
        return [
            'student_id' => 'معرف الطالب',
            'subject_id' => 'معرف المقرر الدراسي',
            'week' => 'رقم الأسبوع',
            'status' => 'حالة الحضور',
        ];
    }

    public function messages()
    {
        return[
            'exists' => 'القيمة المحددة لـ :attribute غير موجودة في النظام.',
            'integer' => 'يجب أن يكون :attribute رقمًا صحيحًا.',
            'min' => 'يجب أن تكون قيمة :attribute على الأقل :min.',
            'max' => 'يجب ألا تتجاوز قيمة :attribute :max.',
            'in' => 'قيمة :attribute غير صالحة. القيم المسموح بها هي: :values.',
        ];
    }
}
