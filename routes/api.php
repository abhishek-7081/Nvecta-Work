<?php

use App\Http\Controllers\Api\NoteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Notes Management API with Rate Limiting (60 requests per minute)
|
*/

Route::middleware(['throttle:60,1'])->group(function () {
    // CRUD Endpoints
    Route::get('/notes', [NoteController::class, 'index']);
    Route::post('/notes', [NoteController::class, 'store']);
    Route::get('/notes/{id}', [NoteController::class, 'show'])->whereNumber('id');
    Route::put('/notes/{id}', [NoteController::class, 'update'])->whereNumber('id');
    Route::delete('/notes/{id}', [NoteController::class, 'destroy'])->whereNumber('id');
});
