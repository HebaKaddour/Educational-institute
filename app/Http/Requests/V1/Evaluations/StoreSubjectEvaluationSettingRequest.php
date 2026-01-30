<?php

namespace App\Http\Requests\V1\Evaluations;

use Mpdf\Tag\A;
use App\Enums\EvaluationType;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSubjectEvaluationSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
       // return auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('teacher'));
       // return auth()->check() && auth()->user()->hasRole('admin');
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
            'evaluation_type' => ['required','string', Rule::in(EvaluationType::arabicValues()),
            ],
            'max_count' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'max_score' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ];
    }


    public function attributes(){
        return [
            'evaluation_type' => 'نوع التقييم',
            'max_score' => 'الدرجة القصوى',
            'max_count' => 'العدد الأقصى',
        ];
    }

    public function messages(): array
    {
        return [
            'evaluation_type.required' => 'نوع التقييم مطلوب',
            'evaluation_type.exists' => 'نوع التقييم غير صالح',
            'max_score.required' => 'الدرجة القصوى مطلوبة',
            'max_score.numeric' => 'الدرجة القصوى يجب أن تكون رقمًا',
            'max_score.min' => 'الدرجة القصوى لا يمكن أن تكون أقل من 0',
            'max_count.required' => 'العدد الأقصى مطلوب',
            'max_count.integer' => 'العدد الأقصى يجب أن يكون عددًا صحيحًا',
            'max_count.min' => 'العدد الأقصى لا يمكن أن يكون أقل من 0',
        ];
    }
}
