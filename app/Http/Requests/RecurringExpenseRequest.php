<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class RecurringExpenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Se estiver atualizando um gasto recorrente existente, garantir pertencimento (FR-024)
        if ($recurringExpense = $this->route('recurring_expense')) {
            return $recurringExpense->user_id === Auth::id();
        }

        return Auth::check();
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('expected_amount') && is_string($this->expected_amount)) {
            $value = trim($this->expected_amount);

            if (str_contains($value, ',')) {
                $cleaned = str_replace('.', '', $value);
                $cleaned = str_replace(',', '.', $cleaned);
                $this->merge([
                    'expected_amount' => is_numeric($cleaned) ? (float) $cleaned : $this->expected_amount,
                ]);
            } elseif (is_numeric($value)) {
                $this->merge([
                    'expected_amount' => (float) $value,
                ]);
            }
        }

        // Default is_active to true if creating or not unchecked
        $this->merge([
            'is_active' => $this->has('is_active') ? $this->boolean('is_active') : true,
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
            'expected_amount' => ['required', 'numeric', 'min:0.01'],
            'frequency' => ['required', 'string', Rule::in(['weekly', 'monthly', 'yearly', 'custom'])],
            'frequency_days' => ['nullable', 'required_if:frequency,custom', 'integer', 'min:1', 'max:365'],
            'due_day' => ['nullable', 'required_if:frequency,monthly', 'integer', 'min:1', 'max:31'],
            'due_date' => ['required', 'date'],
            'billing_document' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Custom validation error messages in Portuguese.
     */
    public function messages(): array
    {
        return [
            'description.required' => 'A descrição do gasto recorrente é obrigatória.',
            'category_id.required' => 'Selecione uma categoria válida.',
            'category_id.exists' => 'A categoria selecionada é inválida ou não pertence a você.',
            'expected_amount.required' => 'O valor esperado é obrigatório.',
            'expected_amount.numeric' => 'O valor esperado deve ser um número válido.',
            'expected_amount.min' => 'O valor esperado deve ser maior que zero.',
            'frequency.required' => 'Selecione a frequência do gasto recorrente.',
            'frequency.in' => 'A frequência selecionada é inválida.',
            'frequency_days.required_if' => 'Informe a quantidade de dias para a frequência personalizada.',
            'frequency_days.min' => 'O intervalo personalizado deve ser de pelo menos 1 dia.',
            'due_day.required_if' => 'Informe o dia do vencimento (1 a 31) para a frequência mensal.',
            'due_day.min' => 'O dia do vencimento deve ser entre 1 e 31.',
            'due_day.max' => 'O dia do vencimento deve ser entre 1 e 31.',
            'due_date.required' => 'A data do primeiro vencimento é obrigatória.',
            'due_date.date' => 'A data do vencimento informada é inválida.',
            'billing_document.file' => 'O documento de cobrança deve ser um arquivo válido.',
            'billing_document.max' => 'O documento de cobrança não pode exceder o tamanho de 10MB.',
            'billing_document.mimes' => 'Formato não suportado. Apenas arquivos PDF, JPG e PNG são aceitos.',
        ];
    }
}
