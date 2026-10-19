<?php

declare(strict_types=1);

namespace App\Modules\Patients\Domain\ValueObjects;

use App\Modules\Patients\Domain\Exceptions\InvalidCpfException;
use Stringable;


final readonly class Cpf implements Stringable
{
    private const LENGTH = 11;

    private function __construct(public string $value) {}

    /**
     * @throws InvalidCpfException
     */
    public static function fromString(string $raw): self
    {
        $digits = self::onlyDigits($raw);

        if (! self::isValid($digits)) {
            throw new InvalidCpfException;
        }

        return new self($digits);
    }

    public static function isValid(string $raw): bool
    {
        $digits = self::onlyDigits($raw);

        if (strlen($digits) !== self::LENGTH) {
            return false;
        }

        // 111.111.111-11 e afins passam no calculo do digito, mas nao existem.
        if (preg_match('/^(\d)\1{10}$/', $digits) === 1) {
            return false;
        }

        // Calcula os dois digitos verificadores: cada um e a soma ponderada
        // dos digitos anteriores, modulo 11.
        foreach ([9, 10] as $position) {
            $sum = 0;

            for ($i = 0; $i < $position; $i++) {
                $sum += (int) $digits[$i] * ($position + 1 - $i);
            }

            $expected = (10 * $sum) % 11 % 10;

            if ((int) $digits[$position] !== $expected) {
                return false;
            }
        }

        return true;
    }

    /** Formato de exibicao: 000.000.000-00 */
    public function formatted(): string
    {
        return vsprintf('%s%s%s.%s%s%s.%s%s%s-%s%s', str_split($this->value));
    }

    public function __toString(): string
    {
        return $this->value;
    }

    private static function onlyDigits(string $raw): string
    {
        return preg_replace('/\D/', '', $raw) ?? '';
    }
}
