<?php

declare(strict_types=1);

namespace App\Modules\Patients\Domain\Rules;

use App\Modules\Patients\Domain\ValueObjects\Cpf;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;


final class ValidCpf implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! Cpf::isValid($value)) {
            $fail('O CPF informado é inválido.');
        }
    }
}
