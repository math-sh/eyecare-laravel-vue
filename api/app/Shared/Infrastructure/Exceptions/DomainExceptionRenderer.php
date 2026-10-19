<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


final class DomainExceptionRenderer
{
    /**
     * Excecao de dominio que nao estiver aqui responde 422.
     *
     * @var array<string, int>
     */
    private const STATUS_MAP = [
        'PatientNotFoundException' => Response::HTTP_NOT_FOUND,
        'CpfAlreadyInUseException' => Response::HTTP_CONFLICT,
        'InvalidCpfException' => Response::HTTP_UNPROCESSABLE_ENTITY,
    ];

    private const MYSQL_DUPLICATE_ENTRY = 1062;

    public static function register(Exceptions $exceptions): void
    {
        // Sem rotas web, qualquer erro em /api/* deve sair em JSON mesmo que o
        // cliente nao mande o header Accept (o curl, por exemplo).
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (DomainException $exception): JsonResponse {
            $name = class_basename($exception);

            return response()->json([
                'error' => $name,
                'message' => $exception->getMessage(),
            ], self::STATUS_MAP[$name] ?? Response::HTTP_UNPROCESSABLE_ENTITY);
        });

        // A checagem da aplicacao perdeu a corrida (duplo submit): o banco
        // barrou o indice unico. Responde 409 em vez de vazar um 500.
        $exceptions->render(function (QueryException $exception): ?JsonResponse {
            if ((int) ($exception->errorInfo[1] ?? 0) !== self::MYSQL_DUPLICATE_ENTRY) {
                return null;
            }

            return response()->json([
                'error' => 'DuplicateEntry',
                'message' => 'Ja existe um registro com esses dados.',
            ], Response::HTTP_CONFLICT);
        });
    }
}
