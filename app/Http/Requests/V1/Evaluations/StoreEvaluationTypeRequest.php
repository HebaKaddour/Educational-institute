<?php

namespace App\Http\Requests\V1\Evaluations;

use Illuminate\Support\Str;
use Illuminate\Foundation\Http\FormRequest;

class StoreEvaluationTypeRequest extends FormRequest
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
          //  'name' => 'required|string|unique:evaluation_types,name',
            'label' => 'sometimes|required|string|max:255|unique:evaluation_types,label',
            'labels' => 'sometimes|required|array|min:1',
            'labels.*' => 'required|string|max:255|distinct|unique:evaluation_types,label',
        ];
    }

    public function validatedLabels(): array
    {
        if ($this->filled('label')) {
            return [$this->label];
        }

        return $this->labels ?? [];
    }
 protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => Str::slug($this->label),
        ]);
    }
    public function attributes(){
        return [
            'name' => 'الاسم الفني',
            'label' => 'انواع التقيمات ',
        ];
    }

    public function messages(): array
    {
        return [

            'label.required' => 'انواع التقييمات مطلوبة',
            'label.string' => 'الاسم المعروض يجب أن يكون نصًا',
            'label.max' => 'الاسم المعروض طويل جدًا',
        ];
    }
}
