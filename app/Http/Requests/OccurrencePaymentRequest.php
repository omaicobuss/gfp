<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class OccurrencePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($occurrence = $this->route('occurrence')) {
            return $occurrence->user_id === Auth::id();
        }

        return Auth::check();
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('actual_amount') && is_string($this->actual_amount)) {
            $value = trim($this->actual_amount);

            if (str_contains($value, ',')) {
                $cleaned = str_replace('.', '', $value);
                $cleaned = str_replace(',', '.', $cleaned);
                $this->merge([
                    'actual_amount' => is_numeric($cleaned) ? (float) $cleaned : $this->actual_amount,
                ]);
            } elseif (is_numeric($value)) {
                $this->merge([
                    'actual_amount' => (float) $value,
                ]);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'actual_amount' => ['required', 'numeric', 'min:0.01'],
            'paid_at' => ['required', 'date'],
            'payment_receipt' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Custom validation messages in Portuguese.
     */
    public function messages(): array
    {
        return [
            'actual_amount.required' => 'O valor pago é obrigatório.',
            'actual_amount.numeric' => 'O valor pago deve ser um número válido.',
            'actual_amount.min' => 'O valor pago deve ser maior que zero.',
            'paid_at.required' => 'A data do pagamento é obrigatória.',
            'paid_at.date' => 'A data do pagamento é inválida.',
            'payment_receipt.file' => 'O comprovante deve ser um arquivo válido.',
            'payment_receipt.max' => 'O comprovante de pagamento não pode exceder 10MB.',
            'payment_receipt.mimes' => 'Formato não suportado. Apenas arquivos PDF, JPG e PNG são aceitos.',
        ];
    }
}
