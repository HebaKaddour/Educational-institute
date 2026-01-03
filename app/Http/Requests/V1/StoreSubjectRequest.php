<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|unique:subjects,name|max:255',
            'teacher_id' => 'required|exists:users,id',
        ];
    }

    protected function prepareForValidation()
    {
        $mergeData = [];

        if ($this->filled('اسم_المادة')) {
            $mergeData['name'] = $this->input('اسم_المادة');
        }

        if ($this->filled('المعلم')) {
            $mergeData['teacher_id'] = $this->input('المعلم');
        }

        $this->merge($mergeData);
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم المادة مطلوب',
            'name.string' => 'اسم المادة يجب أن يكون نصًا',
            'name.unique' => 'اسم المادة مستخدم مسبقًا',
            'name.max' => 'اسم المادة طويل جدًا',
            'teacher_id.required' => 'المعلم مطلوب',
            'teacher_id.exists' => 'المعلم غير موجود',
        ];
    }
}
