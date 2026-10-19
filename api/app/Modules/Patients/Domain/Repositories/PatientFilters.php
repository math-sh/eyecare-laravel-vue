<?php

declare(strict_types=1);

namespace App\Modules\Patients\Domain\Repositories;

/**
 * Criterios de busca da listagem.
 *
 * Mora no dominio, junto do contrato que o recebe: se estivesse em
 * Application, o dominio dependeria da camada de cima.
 */
final readonly class PatientFilters
{
    public function __construct(
        public ?string $search = null,
        public int $page = 1,
        public int $perPage = 10,
    ) {}
}
