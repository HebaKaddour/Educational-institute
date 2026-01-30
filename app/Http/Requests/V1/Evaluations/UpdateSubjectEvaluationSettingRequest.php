<?php

namespace App\Http\Requests\V1\Evaluations;

use App\Enums\EvaluationType;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSubjectEvaluationSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
         return [
            'evaluation_type' => ['sometimes','string',Rule::in(EvaluationType::arabicValues()),
            ],
            'max_count' => [
                'sometimes','nullable',
                'integer',
                'min:0',
            ],

            'max_score' => [
                'sometimes',
                'nullable',
                'integer',
                'min:0',
            ],
        ];
    }
}
