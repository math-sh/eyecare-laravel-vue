<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exceptions;

use RuntimeException;

/**
 * Base de toda excecao de regra de negocio.
 *
 * O dominio nao conhece HTTP: quem traduz a excecao em status e o
 * DomainExceptionRenderer, na camada de infraestrutura.
 */
abstract class DomainException extends RuntimeException {}
