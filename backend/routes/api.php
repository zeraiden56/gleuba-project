<?php

use App\Http\Controllers\Api\MunicipioController;
use App\Models\Municipio;
use Illuminate\Support\Facades\Route;

Route::get('/municipios', [MunicipioController::class, 'index']);
Route::get('/municipios/{id}', [MunicipioController::class, 'show']);
Route::get('/municipios', function () {
    return Municipio::orderByDesc('indice')->get();
});

