<?php

namespace App\Http\Requests\V1\Evaluations;

use Illuminate\Foundation\Http\FormRequest;

class StoreEvaluationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
        'student_id' => 'required|exists:students,id',
        'subject_id' => 'required|exists:subjects,id',
        'evaluation_type_id' => 'required|exists:evaluation_types,id',
        'score' => [
            'nullable',
            'integer',
            'min:0',
            function ($attribute, $value, $fail) {
                $type = \App\Models\EvaluationType::find($this->evaluation_type_id);

                if ($type && $type->uses_score && is_null($value)) {
                    $fail('يجب إدخال درجة لهذا النوع من التقييم');
                }

                if ($type && !$type->uses_score && !is_null($value)) {
                    $fail('هذا النوع من التقييم لا يقبل درجة');
                }
            }
        ],

        'status' => [
            'nullable',
            'in:completed,not_completed',
        ],

        'week' => 'nullable|integer|min:1|max:36',
        'date' => 'required|date',
    ];
    }

    public function attributes()
    {
        return [
            'student_id' => 'معرف الطالب',
            'subject_id' => 'معرف المقرر الدراسي',
            'evaluation_type_id' => 'معرف نوع التقييم',
            'score' => 'الدرجة',
            'status' => 'الحالة',
            'week' => 'رقم الأسبوع',
            'date' => 'التاريخ',
        ];
    }

    public function messages()
    {
        return [
            'required' => 'الحقل :attribute مطلوب.',
            'exists' => 'القيمة المحددة لـ :attribute غير موجودة في النظام.',
            'integer' => 'يجب أن يكون :attribute رقمًا صحيحًا.',
            'min' => 'يجب أن تكون قيمة :attribute على الأقل :min.',
            'max' => 'يجب ألا تتجاوز قيمة :attribute :max.',
            'in' => 'قيمة :attribute غير صالحة. القيم المسموح بها هي: :values.',
            'date' => 'يجب أن يكون :attribute تاريخًا صالحًا.',
        ];
    }
}
