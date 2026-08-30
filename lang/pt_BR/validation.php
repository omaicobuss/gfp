<?php

return [
    'accepted' => 'O campo :attribute deve ser aceito.',
    'confirmed' => 'A confirmação do campo :attribute não confere.',
    'email' => 'O campo :attribute deve ser um endereço de e-mail válido.',
    'max' => [
        'string' => 'O campo :attribute não pode ter mais de :max caracteres.',
        'file' => 'O arquivo no campo :attribute não pode ser maior que :max kilobytes.',
    ],
    'min' => [
        'string' => 'O campo :attribute deve ter pelo menos :min caracteres.',
        'numeric' => 'O campo :attribute deve ser no mínimo :min.',
    ],
    'numeric' => 'O campo :attribute deve ser um número.',
    'required' => 'O campo :attribute é obrigatório.',
    'unique' => 'O :attribute informado já está em uso.',
    'mimes' => 'O arquivo deve ser do tipo: :values.',
    'attributes' => [
        'name' => 'nome',
        'email' => 'e-mail',
        'password' => 'senha',
        'password_confirmation' => 'confirmação de senha',
        'current_password' => 'senha atual',
        'description' => 'descrição',
        'amount' => 'valor',
        'expected_amount' => 'valor esperado',
        'date' => 'data',
        'payment_date' => 'data de pagamento',
        'due_date' => 'data de vencimento',
        'category_id' => 'categoria',
        'billing_document' => 'documento de cobrança',
        'payment_receipt' => 'comprovante de pagamento',
    ],
];
