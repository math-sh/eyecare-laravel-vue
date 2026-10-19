<?php

declare(strict_types=1);

namespace App\Modules\Patients\Domain\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class InvalidCpfException extends DomainException
{
    public function __construct()
    {
        // Sem ecoar o valor recebido: CPF e dado pessoal e a mensagem vai
        // para o cliente e para o log.
        parent::__construct('O CPF informado é inválido.');
    }
}
