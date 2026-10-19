<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Patients;

use App\Modules\Patients\Domain\Entities\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Exercita a API pelo HTTP, do roteamento ao banco.
 */
final class PatientApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function lista_pacientes_paginados(): void
    {
        Patient::factory()->count(12)->create();

        $response = $this->getJson('/api/patients?per_page=5');

        $response->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.total', 12)
            ->assertJsonPath('meta.last_page', 3)
            ->assertJsonStructure([
                'data' => [['id', 'full_name', 'cpf', 'rg', 'birth_date', 'biological_sex', 'cellphone']],
                'meta' => ['total', 'page', 'per_page', 'last_page'],
            ]);
    }

    #[Test]
    public function busca_por_nome_e_por_cpf(): void
    {
        Patient::factory()->create(['full_name' => 'Alfredo Rhiel Madsen', 'cpf' => '52998224725']);
        Patient::factory()->create(['full_name' => 'Justin Baptista', 'cpf' => '71428793860']);

        $this->getJson('/api/patients?search=alfredo')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.full_name', 'Alfredo Rhiel Madsen');

        // O usuario digita o CPF com mascara; a coluna guarda so digitos.
        $this->getJson('/api/patients?search=529.982.247-25')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.cpf', '52998224725');
    }

    #[Test]
    public function cadastra_paciente_normalizando_os_campos(): void
    {
        $response = $this->postJson('/api/patients', $this->payload());

        $response->assertCreated()
            ->assertJsonPath('data.cpf', '52998224725')
            ->assertJsonPath('data.cellphone', '11928535718')
            ->assertJsonPath('data.rg', '356880199');

        $this->assertDatabaseHas('patients', ['cpf' => '52998224725', 'deleted_at' => null]);
    }

    #[Test]
    public function recusa_cpf_invalido(): void
    {
        $this->postJson('/api/patients', $this->payload(['cpf' => '111.111.111-11']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('cpf');

        $this->assertDatabaseCount('patients', 0);
    }

    #[Test]
    public function exige_os_campos_obrigatorios(): void
    {
        $this->postJson('/api/patients', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['full_name', 'cpf', 'rg', 'birth_date', 'biological_sex', 'cellphone']);
    }

    #[Test]
    public function email_e_descricao_sao_opcionais(): void
    {
        $this->postJson('/api/patients', $this->payload(['email' => null, 'description' => null]))
            ->assertCreated()
            ->assertJsonPath('data.email', null)
            ->assertJsonPath('data.description', null);
    }

    #[Test]
    public function recusa_cpf_ja_cadastrado(): void
    {
        Patient::factory()->create(['cpf' => '52998224725']);

        $this->postJson('/api/patients', $this->payload())
            ->assertStatus(409)
            ->assertJsonPath('error', 'CpfAlreadyInUseException');
    }

    #[Test]
    public function exibe_um_paciente(): void
    {
        $patient = Patient::factory()->create(['full_name' => 'Aspen Lipshutz']);

        $this->getJson("/api/patients/{$patient->id}")
            ->assertOk()
            ->assertJsonPath('data.full_name', 'Aspen Lipshutz');
    }

    #[Test]
    public function devolve_404_para_paciente_inexistente(): void
    {
        $this->getJson('/api/patients/8a7b6c5d-0000-0000-0000-000000000000')
            ->assertNotFound()
            ->assertJsonPath('error', 'PatientNotFoundException');
    }

    #[Test]
    public function atualiza_paciente_mantendo_o_proprio_cpf(): void
    {
        $patient = Patient::factory()->create(['cpf' => '52998224725']);

        $this->putJson("/api/patients/{$patient->id}", $this->payload(['full_name' => 'Nome Corrigido']))
            ->assertOk()
            ->assertJsonPath('data.full_name', 'Nome Corrigido');

        $this->assertDatabaseHas('patients', ['id' => $patient->id, 'full_name' => 'Nome Corrigido']);
    }

    #[Test]
    public function recusa_atualizacao_com_cpf_de_outro_paciente(): void
    {
        Patient::factory()->create(['cpf' => '52998224725']);
        $outro = Patient::factory()->create(['cpf' => '71428793860']);

        $this->putJson("/api/patients/{$outro->id}", $this->payload())
            ->assertStatus(409);
    }

    #[Test]
    public function exclusao_e_soft_delete(): void
    {
        $patient = Patient::factory()->create();

        $this->deleteJson("/api/patients/{$patient->id}")->assertNoContent();

        // Sai da listagem e do detalhe...
        $this->getJson('/api/patients')->assertJsonPath('meta.total', 0);
        $this->getJson("/api/patients/{$patient->id}")->assertNotFound();

        // ...mas a linha continua no banco, so que marcada.
        $this->assertSoftDeleted('patients', ['id' => $patient->id]);
    }

    #[Test]
    public function cpf_de_paciente_excluido_continua_bloqueado(): void
    {
        $patient = Patient::factory()->create(['cpf' => '52998224725']);
        $patient->delete();

        // O indice unico do banco segue ocupado: responder 409 evita que o
        // INSERT estoure e vire 500.
        $this->postJson('/api/patients', $this->payload())
            ->assertStatus(409)
            ->assertJsonPath('error', 'CpfAlreadyInUseException');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'full_name' => 'Alfredo Rhiel Madsen',
            'cpf' => '529.982.247-25',
            'rg' => '35.688.019-9',
            'birth_date' => '1996-07-20',
            'biological_sex' => 'male',
            'cellphone' => '(11) 92853-5718',
            'email' => 'alfredo.rhiel@email.com',
            'description' => 'Paciente de teste.',
        ], $overrides);
    }
}
