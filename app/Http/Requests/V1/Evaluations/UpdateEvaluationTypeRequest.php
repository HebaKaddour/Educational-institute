<?php

namespace App\Http\Requests\V1\Evaluations;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEvaluationTypeRequest extends FormRequest
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
            'label' => 'sometimes|string',
           // 'uses_score' => 'required|boolean',
        ];
    }
}
