<?php

use App\Http\Controllers\Api\LicenseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    Route::prefix('license')->group(function () {
        // Check license status (without using credit)
        Route::post('/check', [LicenseController::class, 'check']);
        
        // Verify license (auto deducts credit for credits-based)
        Route::post('/verify', [LicenseController::class, 'verify']);
        
        // Deactivate license from device
        Route::post('/deactivate', [LicenseController::class, 'deactivate']);
    });
});
