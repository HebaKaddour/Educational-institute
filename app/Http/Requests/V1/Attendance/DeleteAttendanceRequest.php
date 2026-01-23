<?php
namespace App\Http\Requests\V1\Attendance;
use Illuminate\Foundation\Http\FormRequest;

class DeleteAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'exists:students,id'],
            'date'       => ['required', 'date'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required' => 'يجب تحديد الطالب',
            'student_id.exists'   => 'الطالب غير موجود',
            'date.required'       => 'يجب تحديد التاريخ',
            'date.date'           => 'صيغة التاريخ غير صحيحة',
            'subject_id.exists'   => 'المادة غير موجودة',
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
}
