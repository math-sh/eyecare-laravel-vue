<?php

declare(strict_types=1);

namespace App\Modules\Patients;

use App\Modules\Patients\Domain\Repositories\PatientRepository;
use App\Modules\Patients\Infrastructure\Repositories\EloquentPatientRepository;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;


final class PatientsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PatientRepository::class, EloquentPatientRepository::class);
    }

    public function boot(): void
    {
        Route::middleware('api')
            ->prefix('api')
            ->group(__DIR__.'/Presentation/routes.php');
    }
}
