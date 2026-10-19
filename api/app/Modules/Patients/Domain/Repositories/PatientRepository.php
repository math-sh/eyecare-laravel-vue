<?php

declare(strict_types=1);

namespace App\Modules\Patients\Domain\Repositories;

use App\Modules\Patients\Domain\Entities\Patient;
use App\Shared\Domain\Pagination\PaginatedResult;

/**
 * Contrato de persistencia de pacientes.
 *
 * A implementacao Eloquent vive em Infrastructure e e ligada a esta interface
 * no PatientsServiceProvider. O service depende so daqui, o que permite
 * testa-lo com um repositorio em memoria, sem banco.
 *
 * Os metodos tem nome de negocio: nada de findBy(array $where) generico, que
 * vazaria o ORM para a camada de aplicacao.
 */
interface PatientRepository
{
    /**
     * @return PaginatedResult<Patient>
     */
    public function paginate(PatientFilters $filters): PaginatedResult;

    public function findById(string $id): ?Patient;

    /**
     * Considera tambem os excluidos: o indice unico do banco continua ocupado
     * por um paciente com soft delete, entao o cadastro precisa enxerga-lo
     * para responder 409 em vez de estourar no INSERT.
     */
    public function existsByCpf(string $cpf, ?string $ignorePatientId = null): bool;

    public function save(Patient $patient): void;

    /** Soft delete: a linha permanece no banco com deleted_at preenchido. */
    public function delete(Patient $patient): void;
}
