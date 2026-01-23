<?php

namespace App\Http\Requests\V1\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSessionRequest extends FormRequest
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
          // 'session_name' => 'required|string|max:255',
            'start_time'   => 'sometimes|date_format:H:i',
            'end_time'     => 'sometimes|date_format:H:i|after:start_time',
        ];
    }

     public function attributes(){
        return [
            'session_name' => 'اسم الحصة',
            'start_time' => 'وقت البداية',
            'end_time' => 'وقت النهاية',
        ];
    }

    public function messages(): array
    {
        return [
            'session_name.required' => 'اسم الحصة مطلوب',
            'session_name.unique' => 'اسم الحصة مستخدم مسبقًا',
            //'start_time.required' => 'وقت البداية مطلوب',
            'start_time.date_format' => 'وقت البداية يجب أن يكون بالتنسيق الصحيح H:i',
           // 'end_time.required' => 'وقت النهاية مطلوب',
            'end_time.date_format' => 'وقت النهاية يجب أن يكون بالتنسيق الصحيح H:i',
            'end_time.after' => 'وقت النهاية يجب أن يكون بعد وقت البداية',
        ];
    }

}
