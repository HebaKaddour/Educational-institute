<?php

namespace App\Http\Requests\V1\Reports;

use Illuminate\Foundation\Http\FormRequest;

class StudentReportRequest extends FormRequest
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
           'grade' => ['sometimes', 'string', 'exists:students,grade'],

           'subject_id' => ['sometimes', 'integer', 'exists:subjects,id'],

        ];
    }

    public function attributes()
    {
        return [
            'grade' => 'الصف الدراسي',
            'subject_id' => 'المادة الدراسية',
        ];
    }

    public function messages(): array
    {
        return [
            'grade.exists' => 'The selected grade is invalid.',
            'subject_id.exists' => 'The selected subject is invalid.',
        ];
    }
}
