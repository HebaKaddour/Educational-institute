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
            'subject_id' => 'required|exists:subjects,id',

            'grades' => 'required|array|min:1',
           'grades.*.student_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    if (!Student::where('id', $value)->exists()) {
                        $index = explode('.', $attribute)[1];
                        $fail("الطالب في الصف رقم {$index} غير موجود في النظام");
                    }
                }
            ],

            'grades.*.evaluations' => 'required|array|min:1',

            'grades.*.evaluations.*.evaluation_type_id'
                => 'required|exists:evaluation_types,id',

            'grades.*.evaluations.*.evaluation_number'
                => 'nullable|integer|min:1',

            'grades.*.evaluations.*.score'
                => 'required|integer|min:0',

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
            'required' => 'حقل :attribute مطلوب.',
            'exists' => 'القيمة المحددة في حقل :attribute غير صالحة.',
            'array' => 'حقل :attribute يجب أن يكون مصفوفة.',
            'min' => 'قيمة حقل :attribute يجب أن تكون على الأقل :min.',
            'integer' => 'حقل :attribute يجب أن يكون عدداً صحيحاً.',
            'nullable' => 'حقل :attribute يمكن أن يكون فارغاً.',

        ];
    }


}
