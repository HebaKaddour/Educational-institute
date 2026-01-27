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
                'date' => 'sometimes|date',
                'gender'  => 'sometimes|in:ذكر,انثى',
                'grade'   => 'sometimes|string',
                'section' => 'sometimes|string|exists:students,section',
                'subject_id' => 'sometimes|nullable|exists:subjects,id',
                'day' => 'sometimes|in:السبت,الأحد,الاثنين,الثلاثاء,الأربعاء,الخميس',
        ];
    }

    public function attributes()
    {
        return [
            'date' => 'التاريخ',
            'gender'  => 'الجنس',
            'grade'   => 'الصف',
            'section' => 'الشعبة',
        ];
    }

    public function messages()
    {
        return[
            'date.required' => 'التاريخ مطلوب',
            'date.date' => 'التاريخ غير صالح',
            'gender.required' => 'الجنس مطلوب',
            'gender.in' => 'الجنس المختار غير صالح',
            'grade.required' => 'الصف مطلوب',
            'section.string' => 'الشعبة غير صالحة',

        ];
    }
}
