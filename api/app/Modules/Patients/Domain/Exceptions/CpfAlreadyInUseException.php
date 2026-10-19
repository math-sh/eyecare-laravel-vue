<?php

declare(strict_types=1);

namespace App\Modules\Patients\Domain\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class CpfAlreadyInUseException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Este CPF já está cadastrado.');
    }
}
