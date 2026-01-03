<?php

namespace App\Http\Requests\V1\Students;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubscriptionStudentRequest extends FormRequest
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
            'payment_amount' => ['required', 'numeric', 'min:1'],
        ];
    }

    public function attributes(): array
    {
        return [
            'payment_amount' => 'قيمة الدفعة',
            'subscription' => 'الاشتراك'
        ];
    }



    public function messages(): array
    {
        return [
            'payment_amount.required' => 'يرجى إدخال قيمة الدفعة',
            'payment_amount.numeric' => 'قيمة الدفعة يجب أن تكون رقمًا',
            'payment_amount.min' => 'قيمة الدفعة يجب أن تكون أكبر من صفر',
        ];
    }
}
