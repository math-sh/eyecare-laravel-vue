<?php

declare(strict_types=1);

namespace App\Modules\Patients\Presentation\Resources;

use App\Modules\Patients\Domain\Entities\Patient;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class PatientResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'cpf' => $this->cpf,
            'rg' => $this->rg,
            'birth_date' => $this->birth_date->toDateString(),
            'biological_sex' => $this->biological_sex->value,
            'cellphone' => $this->cellphone,
            'email' => $this->email,
            'description' => $this->description,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
