<?php

namespace App\Http\Requests\V1\Evaluations;

use Mpdf\Tag\A;
use App\Models\EvaluationType;
use Illuminate\Foundation\Http\FormRequest;

class StoreSubjectEvaluationSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
       // return auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('teacher'));
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
            'evaluation_type' => 'required|exists:evaluation_types,label',
            'evaluation_type_id' => 'required|integer|exists:evaluation_types,id',
            'max_score' => 'required|numeric|min:0',
            'max_count' => 'sometimes|integer|min:0',
        ];
    }

     protected function prepareForValidation(): void
    {
        if ($this->filled('evaluation_type')) {
            $type = EvaluationType::where('label', $this->evaluation_type)->first();

            if ($type) {
                $this->merge([
                    'evaluation_type_id' => $type->id,
                ]);
            }
        }
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
