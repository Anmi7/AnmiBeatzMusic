<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\UploadController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public track API routes
Route::get('/tracks', [TrackController::class, 'index']);
Route::get('/tracks/featured', [TrackController::class, 'featured']);
Route::get('/tracks/genre/{genre}', [TrackController::class, 'byGenre']);
Route::get('/tracks/{id}', [TrackController::class, 'show']);

// Admin-only: upload cover image
Route::post('/upload/cover', [UploadController::class, 'cover'])->middleware('admin.token');
Route::post('/upload/audio', [UploadController::class, 'audio'])->middleware('admin.token');

// Admin-only: create/update/delete tracks
Route::middleware('admin.token')->group(function () {
    Route::post('/tracks', [TrackController::class, 'store']);
    Route::put('/tracks/{id}', [TrackController::class, 'update']);
    Route::delete('/tracks/{id}', [TrackController::class, 'destroy']);
});
