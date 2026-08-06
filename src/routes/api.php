<?php

use App\Http\Controllers\OrganisatieController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'session.fresh'])->group(function (): void {
    Route::get('/organisaties', [OrganisatieController::class, 'index']);
    Route::post('/organisaties', [OrganisatieController::class, 'store']);
    Route::get('/organisaties/{id}', [OrganisatieController::class, 'show']);
    Route::match(['put', 'patch'], '/organisaties/{id}', [OrganisatieController::class, 'update']);
    Route::delete('/organisaties/{id}', [OrganisatieController::class, 'destroy']);
});
