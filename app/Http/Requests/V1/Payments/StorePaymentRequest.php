<?php

namespace App\Http\Requests\V1\Payments;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
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
            'amount'  => 'required|numeric|min:1',
            'method'  => 'required|string|max:100',
            'paid_at' => 'required|date',
            'note'    => 'nullable|string|max:500',
        ];


}

    public function attributes(): array
    {
        return [
            'subscription_id' => 'الاشتراك',
            'paid_at' => 'تاريخ الدفع',
            'method' => 'طريقة الدفع',
            'note' => 'ملاحظة',
            'amount' => 'المبلغ',
        ];
    }

    public function messages(): array
    {
        return [
            'subscription_id.required' => 'حقل الاشتراك مطلوب.',
            'subscription_id.exists' => 'الاشتراك المحدد غير موجود.',
            'paid_at.required' => 'حقل تاريخ الدفع مطلوب.',
            'paid_at.date' => 'حقل تاريخ الدفع يجب أن يكون تاريخًا صالحًا.',
            'method.required' => 'حقل طريقة الدفع مطلوب.',
            'method.string' => 'حقل طريقة الدفع يجب أن يكون نصًا.',
            'method.max' => 'حقل طريقة الدفع لا يجب أن يتجاوز 100 حرف.',
            'note.string' => 'حقل الملاحظة يجب أن يكون نصًا.',
            'note.max' => 'حقل الملاحظة لا يجب أن يتجاوز 500 حرف.',
            'amount.required' => 'حقل المبلغ مطلوب.',
            'amount.numeric' => ' المبلغ يجب أن يكون رقم صحيح.',
            'amount.min' => ' المبلغ المدفوع يجب أن يكون اكبر من 0.',
        ];
    }
}
