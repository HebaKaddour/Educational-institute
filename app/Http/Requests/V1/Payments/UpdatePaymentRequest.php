<?php

namespace App\Http\Requests\V1\Payments;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentRequest extends FormRequest
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
            'amount'  => ['sometimes', 'numeric', 'min:1'],
            'method'  => ['sometimes', 'string'],
            'paid_at' => ['sometimes', 'date'],
            'note'    => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'amount'  => 'قيمة الدفعة',
            'method'  => 'طريقة الدفع',
            'paid_at' => 'تاريخ الدفع',
            'note'    => 'ملاحظة',
        ];
    }
}
