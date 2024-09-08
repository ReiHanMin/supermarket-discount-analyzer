<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DiscountItemController;


Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});

// Define all necessary routes for discount items
Route::get('/discount-items', [DiscountItemController::class, 'index']);
Route::post('/discount-items', [DiscountItemController::class, 'store']);
Route::get('/discount-items/{id}', [DiscountItemController::class, 'show']);
Route::put('/discount-items/{id}', [DiscountItemController::class, 'update']);
Route::delete('/discount-items/{id}', [DiscountItemController::class, 'destroy']);

// Alternatively, you can use the apiResource method to define all routes at once:
// Route::apiResource('discount-items', DiscountItemController::class);

