<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\Controller;

Route::get('/', [Controller::class, 'index']);

Route::middleware(['session.verify'])->group(function () {
    Route::post('/session/create', [SessionController::class, 'create']);
    Route::get('/session/verify/{sessionId}', [SessionController::class, 'verify']);
    Route::delete('/session/delete/{sessionId}', [SessionController::class, 'destroy']);
});
