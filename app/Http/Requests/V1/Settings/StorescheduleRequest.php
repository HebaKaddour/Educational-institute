<?php

namespace App\Http\Requests\V1\Settings;

use App\Enums\SchedulerDay;
use Illuminate\Foundation\Http\FormRequest;

class StorescheduleRequest extends FormRequest
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
           'days' => 'required|array',
           'days.*.day_name' => ['required', 'string', 'in:' . implode(',', array_column(SchedulerDay::cases(), 'value'))],
           'days.*.gender' => 'required|in:ذكور,اناث',
        ];
    }

    public function attributes(){
        return [
            'days' => 'أيام الدوام',
            'days.*.day_name' => 'اسم اليوم',
            'days.gender' => 'الجنس',
        ];
    }

    public function messages(): array
    {
        return [
            'days.required' => 'يجب تحديد أيام الدوام',
            'days.gender.required' => 'الجنس مطلوب لكل يوم',
            'days.array' => 'أيام الدوام يجب أن تكون مصفوفة',
            'days.*.day_name.required' => 'اسم اليوم مطلوب لكل يوم',
            'days.*.day_name.in' => 'اسم اليوم غير صالح  ',
        ];
}
}
