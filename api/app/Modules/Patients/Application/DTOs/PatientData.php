<?php

declare(strict_types=1);

namespace App\Modules\Patients\Application\DTOs;

use App\Modules\Patients\Domain\Enums\BiologicalSex;
use App\Modules\Patients\Domain\ValueObjects\Cpf;

final readonly class PatientData
{
    public function __construct(
        public string $fullName,
        public Cpf $cpf,
        public string $rg,
        public string $birthDate,
        public BiologicalSex $biologicalSex,
        public string $cellphone,
        public ?string $email,
        public ?string $description,
    ) {}

    /**
     * @param  array<string, mixed>  $validated  saida de FormRequest::validated()
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            fullName: trim((string) $validated['full_name']),
            cpf: Cpf::fromString((string) $validated['cpf']),
            rg: self::onlyDigits((string) $validated['rg']),
            birthDate: (string) $validated['birth_date'],
            biologicalSex: BiologicalSex::from((string) $validated['biological_sex']),
            cellphone: self::onlyDigits((string) $validated['cellphone']),
            email: self::nullIfBlank($validated['email'] ?? null),
            description: self::nullIfBlank($validated['description'] ?? null),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'full_name' => $this->fullName,
            'cpf' => $this->cpf->value,
            'rg' => $this->rg,
            'birth_date' => $this->birthDate,
            'biological_sex' => $this->biologicalSex,
            'cellphone' => $this->cellphone,
            'email' => $this->email,
            'description' => $this->description,
        ];
    }

    private static function onlyDigits(string $value): string
    {
        return preg_replace('/\D/', '', $value) ?? '';
    }

    private static function nullIfBlank(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : null;

        return ($value === null || $value === '') ? null : $value;
    }
}
