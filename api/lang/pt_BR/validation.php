<?php

declare(strict_types=1);


return [
    'required' => 'O campo :attribute é obrigatório.',
    'string' => 'O campo :attribute deve ser um texto.',
    'integer' => 'O campo :attribute deve ser um número inteiro.',
    'email' => 'Informe um :attribute válido.',
    'date' => 'O campo :attribute não é uma data válida.',
    'date_format' => 'O campo :attribute deve estar no formato :format.',
    'before' => 'O campo :attribute deve ser uma data anterior a :date.',
    'after' => 'O campo :attribute deve ser uma data posterior a :date.',
    'regex' => 'O formato do campo :attribute é inválido.',
    'enum' => 'O valor selecionado para :attribute é inválido.',
    'in' => 'O valor selecionado para :attribute é inválido.',

    'min' => [
        'string' => 'O campo :attribute deve ter no mínimo :min caracteres.',
        'numeric' => 'O campo :attribute deve ser no mínimo :min.',
    ],
    'max' => [
        'string' => 'O campo :attribute deve ter no máximo :max caracteres.',
        'numeric' => 'O campo :attribute não pode ser maior que :max.',
    ],
];
