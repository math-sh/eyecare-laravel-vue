<?php

declare(strict_types=1);

namespace App\Modules\Patients\Domain\Entities;

use App\Modules\Patients\Domain\Enums\BiologicalSex;
use Database\Factories\PatientFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * O model Eloquent E a entidade de dominio.
 *
 * Separar entidade pura de modelo de persistencia custaria um mapper e a
 * duplicacao de cada campo; o ganho (trocar de ORM) nao se paga aqui. Em troca,
 * a entidade nunca sai crua pela API: quem decide o que vai para o cliente e o
 * PatientResource.
 *
 * @property string $id
 * @property string $full_name
 * @property string $cpf
 * @property string $rg
 * @property Carbon $birth_date
 * @property BiologicalSex $biological_sex
 * @property string $cellphone
 * @property string|null $email
 * @property string|null $description
 */
class Patient extends Model
{
    /** @use HasFactory<PatientFactory> */
    use HasFactory;

    use HasUuids;
    use SoftDeletes;

    protected $table = 'patients';

    protected $fillable = [
        'full_name',
        'cpf',
        'rg',
        'birth_date',
        'biological_sex',
        'cellphone',
        'email',
        'description',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'biological_sex' => BiologicalSex::class,
        ];
    }


    protected static function newFactory(): Factory
    {
        return PatientFactory::new();
    }
}
