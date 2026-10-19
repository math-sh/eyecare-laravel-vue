<?php

declare(strict_types=1);

namespace App\Modules\Patients\Presentation\Requests;

use App\Modules\Patients\Domain\Repositories\PatientFilters;
use Illuminate\Foundation\Http\FormRequest;

class ListPatientsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function toFilters(): PatientFilters
    {
        return new PatientFilters(
            search: $this->validated('search'),
            page: (int) ($this->validated('page') ?? 1),
            perPage: (int) ($this->validated('per_page') ?? 10),
        );
    }
}
