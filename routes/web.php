<?php

use App\Http\Controllers\FloodController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FloodController::class, 'page'])->defaults('page', 'map')->name('map');
Route::get('/perbandingan', [FloodController::class, 'page'])->defaults('page', 'comparison')->name('comparison');
Route::get('/data-sampel', [FloodController::class, 'dataset'])->name('dataset');
Route::get('/analisis', [FloodController::class, 'page'])->defaults('page', 'prediction')->name('prediction');
Route::get('/metodologi', [FloodController::class, 'page'])->defaults('page', 'methodology')->name('methodology');
Route::get('/api/titik', [FloodController::class, 'points'])->name('points');
Route::get('/ekspor', [FloodController::class, 'export'])->name('export');
Route::post('/api/analisis', [FloodController::class, 'predict'])->middleware('throttle:30,1')->name('predict');
