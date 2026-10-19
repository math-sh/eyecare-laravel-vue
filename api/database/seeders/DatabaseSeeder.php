<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Patients\Domain\Entities\Patient;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Patient::factory()->count(35)->create();
    }
}
