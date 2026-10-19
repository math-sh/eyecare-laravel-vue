<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Patients\Domain\ValueObjects;

use App\Modules\Patients\Domain\Exceptions\InvalidCpfException;
use App\Modules\Patients\Domain\ValueObjects\Cpf;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Teste de unidade puro: nao sobe o Laravel nem toca no banco.
 */
final class CpfTest extends TestCase
{
    #[Test]
    #[DataProvider('cpfsValidos')]
    public function aceita_cpf_valido(string $entrada, string $esperado): void
    {
        $this->assertSame($esperado, Cpf::fromString($entrada)->value);
    }

    /** @return array<string, array{string, string}> */
    public static function cpfsValidos(): array
    {
        return [
            'apenas digitos' => ['52998224725', '52998224725'],
            'formatado' => ['529.982.247-25', '52998224725'],
            'com espacos' => [' 529 982 247 25 ', '52998224725'],
            'digito verificador zero' => ['71428793860', '71428793860'],
        ];
    }

    #[Test]
    #[DataProvider('cpfsInvalidos')]
    public function rejeita_cpf_invalido(string $entrada): void
    {
        $this->assertFalse(Cpf::isValid($entrada));

        $this->expectException(InvalidCpfException::class);
        Cpf::fromString($entrada);
    }

    /** @return array<string, array{string}> */
    public static function cpfsInvalidos(): array
    {
        return [
            'digito verificador errado' => ['52998224724'],
            'todos os digitos iguais' => ['11111111111'],
            'zeros' => ['00000000000'],
            'curto demais' => ['5299822472'],
            'longo demais' => ['529982247251'],
            'vazio' => [''],
            'so letras' => ['abcdefghijk'],
        ];
    }

    #[Test]
    public function formata_para_exibicao(): void
    {
        $this->assertSame('529.982.247-25', Cpf::fromString('52998224725')->formatted());
    }

    #[Test]
    public function converte_para_string_sem_mascara(): void
    {
        $this->assertSame('52998224725', (string) Cpf::fromString('529.982.247-25'));
    }
}
