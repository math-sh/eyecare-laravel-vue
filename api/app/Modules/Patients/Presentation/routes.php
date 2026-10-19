<?php

declare(strict_types=1);

use App\Modules\Patients\Presentation\Controllers\PatientController;
use Illuminate\Support\Facades\Route;


Route::prefix('patients')->group(function (): void {
    Route::get('/', [PatientController::class, 'index']);
    Route::post('/', [PatientController::class, 'store']);
    Route::get('{patient}', [PatientController::class, 'show']);
    Route::put('{patient}', [PatientController::class, 'update']);
    Route::delete('{patient}', [PatientController::class, 'destroy']);
});
