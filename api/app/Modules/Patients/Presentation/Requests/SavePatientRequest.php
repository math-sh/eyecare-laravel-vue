<?php

declare(strict_types=1);

namespace App\Modules\Patients\Presentation\Requests;

use App\Modules\Patients\Domain\Enums\BiologicalSex;
use App\Modules\Patients\Domain\Rules\ValidCpf;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class SavePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'min:3', 'max:255'],
            'cpf' => ['required', 'string', new ValidCpf],
            'rg' => ['required', 'string', 'max:20'],
            'birth_date' => ['required', 'date_format:Y-m-d', 'before:today', 'after:1900-01-01'],
            'biological_sex' => ['required', Rule::enum(BiologicalSex::class)],
            'cellphone' => ['required', 'string', 'regex:/^\D*\d{2}\D*9\d{4}\D*\d{4}\D*$/'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cellphone.regex' => 'Informe um celular válido com DDD.',
            'birth_date.before' => 'A data de nascimento deve ser anterior a hoje.',
            'birth_date.date_format' => 'Informe a data de nascimento no formato AAAA-MM-DD.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'full_name' => 'nome completo',
            'cpf' => 'CPF',
            'rg' => 'RG',
            'birth_date' => 'data de nascimento',
            'biological_sex' => 'sexo biológico',
            'cellphone' => 'celular',
            'email' => 'e-mail',
            'description' => 'descrição',
        ];
    }
}
