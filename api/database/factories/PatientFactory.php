<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Patients\Domain\Entities\Patient;
use App\Modules\Patients\Domain\Enums\BiologicalSex;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Patient>
 */
class PatientFactory extends Factory
{
    protected $model = Patient::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $sex = fake()->randomElement(BiologicalSex::cases());

        return [

            'full_name' => fake()->firstName($sex === BiologicalSex::Male ? 'male' : 'female')
                .' '.fake()->lastName().' '.fake()->lastName(),
            'cpf' => fake()->unique()->cpf(false),
            'rg' => fake()->numerify('#########'),
            'birth_date' => fake()->dateTimeBetween('-90 years', '-1 year')->format('Y-m-d'),
            'biological_sex' => $sex,
            'cellphone' => '11'.fake()->numerify('9########'),
            'email' => fake()->optional(0.8)->safeEmail(),
            'description' => fake()->optional(0.5)->paragraph(),
        ];
    }
}
