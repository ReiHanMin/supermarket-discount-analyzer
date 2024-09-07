<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DiscountItemController;

Route::any('adminer', function () {
    return redirect('adminer-launcher.php');
});

// Add this line to handle API routes
Route::prefix('api')->group(base_path('routes/api.php'));

// Serve the Vue application for all non-API routes
Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!api|adminer).*$');