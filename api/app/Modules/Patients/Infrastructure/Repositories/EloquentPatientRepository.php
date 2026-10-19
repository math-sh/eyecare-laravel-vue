<?php

declare(strict_types=1);

namespace App\Modules\Patients\Infrastructure\Repositories;

use App\Modules\Patients\Domain\Entities\Patient;
use App\Modules\Patients\Domain\Repositories\PatientFilters;
use App\Modules\Patients\Domain\Repositories\PatientRepository;
use App\Shared\Domain\Pagination\PaginatedResult;
use Illuminate\Contracts\Database\Query\Builder;

/**
 * Unico ponto do modulo que conhece o Eloquent.
 */
final class EloquentPatientRepository implements PatientRepository
{
    public function paginate(PatientFilters $filters): PaginatedResult
    {
        $query = Patient::query()->latest('created_at');

        $search = trim((string) $filters->search);

        if ($search !== '') {
            $digits = preg_replace('/\D/', '', $search) ?? '';

            $query->where(function (Builder $builder) use ($search, $digits): void {
                $builder->where('full_name', 'like', '%'.$this->escapeLike($search).'%');


                if ($digits !== '') {
                    $builder->orWhere('cpf', 'like', $this->escapeLike($digits).'%');
                }
            });
        }

        $paginator = $query->paginate(perPage: $filters->perPage, page: $filters->page);

        return new PaginatedResult(
            items: array_values($paginator->items()),
            total: $paginator->total(),
            page: $paginator->currentPage(),
            perPage: $paginator->perPage(),
        );
    }

    public function findById(string $id): ?Patient
    {
        return Patient::query()->find($id);
    }

    public function existsByCpf(string $cpf, ?string $ignorePatientId = null): bool
    {
        return Patient::withTrashed()
            ->where('cpf', $cpf)
            ->when($ignorePatientId !== null, fn (Builder $query) => $query->whereKeyNot($ignorePatientId))
            ->exists();
    }

    public function save(Patient $patient): void
    {
        $patient->save();
    }

    public function delete(Patient $patient): void
    {
        $patient->delete();
    }


    private function escapeLike(string $value): string
    {
        return addcslashes($value, '%_\\');
    }
}
