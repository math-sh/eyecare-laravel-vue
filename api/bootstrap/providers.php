<?php

use App\Modules\Patients\PatientsServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    // Um provider por modulo (ver app/Modules/<Modulo>/<Modulo>ServiceProvider).
    PatientsServiceProvider::class,
];
