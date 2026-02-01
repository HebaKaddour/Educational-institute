<?php

namespace App\Http\Requests\V1\Evaluations;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEvaluationRequest extends FormRequest
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
            'score' => ['sometimes', 'integer', 'min:0'],
            'evaluation_number' => ['nullable', 'integer', 'min:1'],
            'evaluation_date' => ['nullable', 'date'],
        ];
    }
}
