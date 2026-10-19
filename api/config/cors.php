<?php

/**
 * Allow-list por variavel de ambiente, nunca '*'.
 *
 * FRONTEND_URL aceita varias origens separadas por virgula — em
 * desenvolvimento o painel responde tanto em localhost quanto em 127.0.0.1,
 * e para o navegador essas duas origens sao diferentes.
 */
$origins = array_values(array_filter(array_map(
    'trim',
    explode(',', (string) env('FRONTEND_URL', 'http://localhost:5173,http://127.0.0.1:5173')),
)));

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => $origins,
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
