<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DiscountItemController;


Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});

// Keep your existing routes commented out for now
// Route::apiResource('discount-items', DiscountItemController::class);

Route::get('/discount-items', [DiscountItemController::class, 'index']);

Route::post('/discount-items', [DiscountItemController::class, 'store']);
