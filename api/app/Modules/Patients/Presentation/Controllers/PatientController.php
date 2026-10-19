<?php

declare(strict_types=1);

namespace App\Modules\Patients\Presentation\Controllers;

use App\Modules\Patients\Application\DTOs\PatientData;
use App\Modules\Patients\Application\Services\PatientService;
use App\Modules\Patients\Presentation\Requests\ListPatientsRequest;
use App\Modules\Patients\Presentation\Requests\SavePatientRequest;
use App\Modules\Patients\Presentation\Resources\PatientResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;


final class PatientController
{
    public function __construct(private readonly PatientService $patients) {}

    public function index(ListPatientsRequest $request): JsonResponse
    {
        $result = $this->patients->list($request->toFilters());

        return response()->json([
            'data' => PatientResource::collection($result->items)->resolve(),
            'meta' => [
                'total' => $result->total,
                'page' => $result->page,
                'per_page' => $result->perPage,
                'last_page' => $result->lastPage(),
            ],
        ]);
    }

    public function store(SavePatientRequest $request): JsonResponse
    {
        $patient = $this->patients->create(PatientData::fromArray($request->validated()));

        return PatientResource::make($patient)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(string $patient): JsonResponse
    {
        return PatientResource::make($this->patients->findById($patient))->response();
    }

    public function update(SavePatientRequest $request, string $patient): JsonResponse
    {
        $updated = $this->patients->update($patient, PatientData::fromArray($request->validated()));

        return PatientResource::make($updated)->response();
    }

    public function destroy(string $patient): Response
    {
        $this->patients->delete($patient);

        return response()->noContent();
    }
}
