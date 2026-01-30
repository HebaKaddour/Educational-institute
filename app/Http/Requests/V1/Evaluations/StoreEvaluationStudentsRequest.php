<?php

namespace App\Http\Requests\V1\Evaluations;

use App\Models\Student;
use App\Models\EvaluationType;
use Illuminate\Foundation\Http\FormRequest;

class StoreEvaluationStudentsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('teacher'));
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

            'grades' => ['required', 'array'],
            'grades.*.student_id' => ['required', 'exists:students,id'],

            'grades.*.evaluations' => ['required', 'array'],
            'grades.*.evaluations.*.evaluation_type' => ['required', 'string'],
            'grades.*.evaluations.*.score' => ['required', 'numeric', 'min:0'],
            'grades.*.evaluations.*.evaluation_number' => ['nullable', 'integer', 'min:1'],
            'grades.*.evaluations.*.evaluation_date' => ['nullable', 'date'],
        ];
    }

    public function attributes()
    {
        return [
            'subject_id' => 'المادة',
            'student_id' => 'الطالب',
            'grades' => 'الدرجات',
            'grades.*.student_id' => 'الطالب',
            'grades.*.evaluations' => 'التقييمات',
            'grades.*.evaluations.*.student_id' => 'الطالب',
            'grades.*.evaluations.*.evaluation_number' => 'رقم التقييم',
            'grades.*.evaluations.*.score' => 'الدرجة',
            'grades.*.evaluations.*.evaluation_type_id' => 'نوع التقييم',
            'grades.*.evaluation_type' => 'نوع التقييم',
            'grades.*.evaluation_number' => 'رقم التقييم',
            'grades.*.score' => 'الدرجة',
            'grades.*.evaluation_type_id' => 'required|integer|exists:evaluation_types,id',
        ];
    }

    public function messages()
    {
        return [
            'subject_id.required' => 'المادة مطلوبة',
            'grades.required' => 'بيانات الطلاب مطلوبة',
            'grades.*.student_id.required' => 'الطالب مطلوب',
            'grades.*.evaluations.required' => 'التقييمات مطلوبة',
            'grades.*.evaluations.*.evaluation_type.required' => 'نوع التقييم مطلوب',
            'grades.*.evaluations.*.score.required' => 'الدرجة مطلوبة',

        ];
    }


}
