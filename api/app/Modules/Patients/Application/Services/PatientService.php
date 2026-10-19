<?php

declare(strict_types=1);

namespace App\Modules\Patients\Application\Services;

use App\Modules\Patients\Application\DTOs\PatientData;
use App\Modules\Patients\Domain\Entities\Patient;
use App\Modules\Patients\Domain\Exceptions\CpfAlreadyInUseException;
use App\Modules\Patients\Domain\Exceptions\PatientNotFoundException;
use App\Modules\Patients\Domain\Repositories\PatientFilters;
use App\Modules\Patients\Domain\Repositories\PatientRepository;
use App\Shared\Domain\Pagination\PaginatedResult;

/**
 * Casos de uso de paciente.
 *
 * Depende do contrato PatientRepository, nunca do Eloquent: e o que permite
 * testar esta classe com um repositorio em memoria, sem subir banco.
 */
final readonly class PatientService
{
    public function __construct(private PatientRepository $patients) {}

    /**
     * @return PaginatedResult<Patient>
     */
    public function list(PatientFilters $filters): PaginatedResult
    {
        return $this->patients->paginate($filters);
    }

    /**
     * @throws PatientNotFoundException
     */
    public function findById(string $id): Patient
    {
        return $this->patients->findById($id)
            ?? throw new PatientNotFoundException;
    }

    /**
     * @throws CpfAlreadyInUseException
     */
    public function create(PatientData $data): Patient
    {
        $this->assertCpfIsAvailable($data);

        $patient = new Patient;
        $patient->fill($data->toAttributes());

        $this->patients->save($patient);

        return $patient;
    }

    /**
     * @throws PatientNotFoundException
     * @throws CpfAlreadyInUseException
     */
    public function update(string $id, PatientData $data): Patient
    {
        $patient = $this->findById($id);

        $this->assertCpfIsAvailable($data, ignorePatientId: $patient->id);

        $patient->fill($data->toAttributes());

        $this->patients->save($patient);

        return $patient;
    }

    /**
     * @throws PatientNotFoundException
     */
    public function delete(string $id): void
    {
        $this->patients->delete($this->findById($id));
    }

    /**
     * @throws CpfAlreadyInUseException
     */
    private function assertCpfIsAvailable(PatientData $data, ?string $ignorePatientId = null): void
    {
        if ($this->patients->existsByCpf($data->cpf->value, $ignorePatientId)) {
            throw new CpfAlreadyInUseException;
        }
    }
}
