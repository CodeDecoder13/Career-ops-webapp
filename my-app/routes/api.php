<?php

use App\Http\Controllers\SyncController;
use Illuminate\Support\Facades\Route;

Route::post('/sync', [SyncController::class, 'store'])
    ->middleware('sync.token')
    ->name('api.sync');
