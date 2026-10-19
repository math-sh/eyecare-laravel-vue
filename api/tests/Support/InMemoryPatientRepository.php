<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Modules\Patients\Domain\Entities\Patient;
use App\Modules\Patients\Domain\Repositories\PatientFilters;
use App\Modules\Patients\Domain\Repositories\PatientRepository;
use App\Shared\Domain\Pagination\PaginatedResult;
use Illuminate\Support\Str;



final class InMemoryPatientRepository implements PatientRepository
{
    /** @var array<string, Patient> */
    private array $patients = [];

    /** @var array<string, true> */
    private array $deleted = [];

    public function paginate(PatientFilters $filters): PaginatedResult
    {
        $matches = array_values(array_filter(
            $this->alive(),
            fn (Patient $patient) => $filters->search === null
                || str_contains(mb_strtolower($patient->full_name), mb_strtolower($filters->search))
                || str_contains($patient->cpf, preg_replace('/\D/', '', $filters->search) ?: $filters->search),
        ));

        return new PaginatedResult(
            items: array_slice($matches, ($filters->page - 1) * $filters->perPage, $filters->perPage),
            total: count($matches),
            page: $filters->page,
            perPage: $filters->perPage,
        );
    }

    public function findById(string $id): ?Patient
    {
        return isset($this->deleted[$id]) ? null : ($this->patients[$id] ?? null);
    }

    public function existsByCpf(string $cpf, ?string $ignorePatientId = null): bool
    {
        // Olha tambem os excluidos, como manda o contrato.
        foreach ($this->patients as $patient) {
            if ($patient->cpf === $cpf && $patient->id !== $ignorePatientId) {
                return true;
            }
        }

        return false;
    }

    public function save(Patient $patient): void
    {
        $patient->id ??= (string) Str::uuid();

        $this->patients[$patient->id] = $patient;
    }

    public function delete(Patient $patient): void
    {
        $this->deleted[$patient->id] = true;
    }

    /** @return array<string, Patient> */
    private function alive(): array
    {
        return array_diff_key($this->patients, $this->deleted);
    }
}
