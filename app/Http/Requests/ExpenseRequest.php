<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ExpenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Se estiver atualizando ou manipulando um gasto existente, garantir que pertence ao usuário (FR-024)
        if ($expense = $this->route('expense')) {
            return $expense->user_id === Auth::id();
        }

        return Auth::check();
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('amount') && is_string($this->amount)) {
            $value = trim($this->amount);

            // Se contiver vírgula (formato brasileiro Ex: "1.250,50" ou "350,50"), converter para float
            if (str_contains($value, ',')) {
                $cleaned = str_replace('.', '', $value);
                $cleaned = str_replace(',', '.', $cleaned);
                $this->merge([
                    'amount' => is_numeric($cleaned) ? (float) $cleaned : $this->amount,
                ]);
            } elseif (is_numeric($value)) {
                $this->merge([
                    'amount' => (float) $value,
                ]);
            }
        }

        $this->merge([
            'is_paid' => $this->boolean('is_paid'),
        ]);
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
            'amount' => ['required', 'numeric', 'min:0.01'],
            'date' => ['required', 'date'],
            'is_paid' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Custom validation error messages in Portuguese.
     */
    public function messages(): array
    {
        return [
            'description.required' => 'A descrição do gasto é obrigatória.',
            'category_id.required' => 'Selecione uma categoria para o gasto.',
            'category_id.exists' => 'A categoria selecionada é inválida ou não pertence a você.',
            'amount.required' => 'O valor do gasto é obrigatório.',
            'amount.numeric' => 'O valor informado deve ser numérico.',
            'amount.min' => 'O valor do gasto deve ser maior que zero.',
            'date.required' => 'A data do gasto é obrigatória.',
            'date.date' => 'A data informada é inválida.',
        ];
    }
}
