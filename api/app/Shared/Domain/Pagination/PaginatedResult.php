<?php

declare(strict_types=1);

namespace App\Shared\Domain\Pagination;

/**
 * Resultado paginado independente de ORM.
 *
 * Existe para o repositorio devolver pagina sem vazar o LengthAwarePaginator
 * do Eloquent para a camada de aplicacao.
 *
 * @template T
 */
final readonly class PaginatedResult
{
    /**
     * @param  list<T>  $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $page,
        public int $perPage,
    ) {}

    public function lastPage(): int
    {
        return max(1, (int) ceil($this->total / $this->perPage));
    }
}
