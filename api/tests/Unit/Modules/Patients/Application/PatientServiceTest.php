<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Patients\Application;

use App\Modules\Patients\Application\DTOs\PatientData;
use App\Modules\Patients\Application\Services\PatientService;
use App\Modules\Patients\Domain\Exceptions\CpfAlreadyInUseException;
use App\Modules\Patients\Domain\Exceptions\PatientNotFoundException;
use App\Modules\Patients\Domain\Repositories\PatientFilters;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\InMemoryPatientRepository;
use Tests\TestCase;

/**
 * O service e testado contra o contrato do repositorio, com uma implementacao
 * em memoria: nenhuma query e executada.
 *
 * Sobe o container do Laravel porque o cast de data do Eloquent pede um
 * resolver de conexao, mas nenhuma conexao chega a ser aberta. Se a inversao
 * de dependencia nao estivesse correta, este teste precisaria de banco.
 */
final class PatientServiceTest extends TestCase
{
    private InMemoryPatientRepository $repository;

    private PatientService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new InMemoryPatientRepository;
        $this->service = new PatientService($this->repository);
    }

    #[Test]
    public function cria_paciente_normalizando_cpf_e_celular(): void
    {
        $patient = $this->service->create($this->dados(cpf: '529.982.247-25', cellphone: '(11) 92853-5718'));

        $this->assertSame('52998224725', $patient->cpf);
        $this->assertSame('11928535718', $patient->cellphone);
        $this->assertNotNull($this->repository->findById($patient->id));
    }

    #[Test]
    public function recusa_cadastro_com_cpf_ja_usado(): void
    {
        $this->service->create($this->dados(cpf: '52998224725'));

        $this->expectException(CpfAlreadyInUseException::class);
        $this->service->create($this->dados(cpf: '529.982.247-25', fullName: 'Outra Pessoa'));
    }

    #[Test]
    public function permite_editar_paciente_mantendo_o_proprio_cpf(): void
    {
        $patient = $this->service->create($this->dados(cpf: '52998224725'));

        $atualizado = $this->service->update($patient->id, $this->dados(
            cpf: '52998224725',
            fullName: 'Nome Corrigido',
        ));

        $this->assertSame('Nome Corrigido', $atualizado->full_name);
    }

    #[Test]
    public function recusa_edicao_que_rouba_o_cpf_de_outro_paciente(): void
    {
        $this->service->create($this->dados(cpf: '52998224725'));
        $outro = $this->service->create($this->dados(cpf: '71428793860', fullName: 'Segundo'));

        $this->expectException(CpfAlreadyInUseException::class);
        $this->service->update($outro->id, $this->dados(cpf: '52998224725', fullName: 'Segundo'));
    }

    #[Test]
    public function excluir_remove_da_listagem(): void
    {
        $patient = $this->service->create($this->dados());

        $this->service->delete($patient->id);

        $this->assertSame(0, $this->service->list(new PatientFilters)->total);
    }

    #[Test]
    public function cpf_de_paciente_excluido_continua_bloqueado(): void
    {
        $patient = $this->service->create($this->dados(cpf: '52998224725'));
        $this->service->delete($patient->id);

        // O indice unico do banco continua ocupado pela linha com soft delete.
        $this->expectException(CpfAlreadyInUseException::class);
        $this->service->create($this->dados(cpf: '52998224725', fullName: 'Reaproveitando'));
    }

    #[Test]
    public function buscar_paciente_inexistente_falha(): void
    {
        $this->expectException(PatientNotFoundException::class);
        $this->service->findById('nao-existe');
    }

    #[Test]
    public function filtra_por_nome_e_por_cpf(): void
    {
        $this->service->create($this->dados(cpf: '52998224725', fullName: 'Ana Silva'));
        $this->service->create($this->dados(cpf: '71428793860', fullName: 'Bruno Souza'));

        $this->assertSame(1, $this->service->list(new PatientFilters(search: 'silva'))->total);
        $this->assertSame(1, $this->service->list(new PatientFilters(search: '529.982.247-25'))->total);
        $this->assertSame(2, $this->service->list(new PatientFilters)->total);
    }

    private function dados(
        string $cpf = '52998224725',
        string $fullName = 'Alfredo Rhiel Madsen',
        string $cellphone = '11928535718',
    ): PatientData {
        return PatientData::fromArray([
            'full_name' => $fullName,
            'cpf' => $cpf,
            'rg' => '35.688.019-9',
            'birth_date' => '1996-07-20',
            'biological_sex' => 'male',
            'cellphone' => $cellphone,
            'email' => 'alfredo.rhiel@email.com',
            'description' => null,
        ]);
    }
}
