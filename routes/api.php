<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Product\ProductController;
use App\Http\Controllers\API\QueueTickController;

Route::get('/products', [ProductController::class, 'index']);
Route::post('/products', [ProductController::class, 'store']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::put('/products/{product}', [ProductController::class, 'update']);
Route::patch('/products/{product}', [ProductController::class, 'update']);
Route::delete('/products/{product}', [ProductController::class, 'destroy']);
Route::post('/queue/tick', [QueueTickController::class, 'tick']);
Route::get('/queue/tick', [QueueTickController::class, 'tick']);