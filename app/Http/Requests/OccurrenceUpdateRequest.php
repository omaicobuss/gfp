<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class OccurrenceUpdateRequest extends FormRequest
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
        foreach (['expected_amount', 'actual_amount'] as $field) {
            if ($this->has($field) && is_string($this->$field)) {
                $value = trim($this->$field);

                if (str_contains($value, ',')) {
                    $cleaned = str_replace('.', '', $value);
                    $cleaned = str_replace(',', '.', $cleaned);
                    $this->merge([
                        $field => is_numeric($cleaned) ? (float) $cleaned : $this->$field,
                    ]);
                } elseif (is_numeric($value)) {
                    $this->merge([
                        $field => (float) $value,
                    ]);
                }
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
            'description' => ['required', 'string', 'max:255'],
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where(function ($query) {
                    $query->where('user_id', Auth::id());
                }),
            ],
            'due_date' => ['required', 'date'],
            'expected_amount' => ['required', 'numeric', 'min:0.01'],
            'actual_amount' => ['nullable', 'numeric', 'min:0.01'],
            'paid_at' => ['nullable', 'date'],
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
            'description.required' => 'A descrição da ocorrência é obrigatória.',
            'category_id.required' => 'Selecione uma categoria válida.',
            'category_id.exists' => 'A categoria selecionada é inválida ou não pertence a você.',
            'due_date.required' => 'A data de vencimento é obrigatória.',
            'due_date.date' => 'A data de vencimento é inválida.',
            'expected_amount.required' => 'O valor esperado é obrigatório.',
            'expected_amount.numeric' => 'O valor esperado deve ser um número válido.',
            'payment_receipt.max' => 'O comprovante não pode exceder 10MB.',
            'payment_receipt.mimes' => 'Formato não suportado. Apenas PDF, JPG e PNG são aceitos.',
        ];
    }
}
