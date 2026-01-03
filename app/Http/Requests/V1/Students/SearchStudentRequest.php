<?php

namespace App\Http\Requests\V1\Students;

use Illuminate\Foundation\Http\FormRequest;

class SearchStudentRequest extends FormRequest
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
            'query' => 'required|string|min:2'
        ];
    }

    public function messages(): array
    {
        return [
            'query.required' => 'يجب إدخال اسم الطالب',
            'query.min' => 'يجب إدخال حرفين على الأقل للبحث',
        ];
    }
}
