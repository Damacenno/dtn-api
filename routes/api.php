<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DtnSyncController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/dtn/sync', [DtnSyncController::class, 'sync']);

Route::get('/dtn/delivered-ids', [
    DtnSyncController::class,
    'deliveredIds',
]);
