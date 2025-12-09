<?php

use App\Http\Controllers\SocialiteController;
use App\Livewire\Admin\ApiKeys;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\Orders;
use App\Livewire\Admin\PaymentMethods;
use App\Livewire\Admin\SubscriptionPlans;
use App\Livewire\Admin\Users;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\User\Dashboard;
use App\Livewire\User\History;
use App\Livewire\User\KeywordGenerator;
use App\Livewire\User\MetadataGenerator;
use App\Livewire\User\Settings;
use App\Livewire\User\Subscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('login', Login::class)->name('login');
    Route::get('register', Register::class)->name('register');
    
    // Google OAuth
    Route::get('/auth/google', [SocialiteController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [SocialiteController::class, 'handleGoogleCallback']);
});

/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/generate', MetadataGenerator::class)->name('generate');
    Route::get('/keywords', KeywordGenerator::class)->name('keywords');
    Route::get('/history', History::class)->name('history');
    Route::get('/subscription', Subscription::class)->name('subscription');
    Route::get('/settings', Settings::class)->name('settings');
    
    // Logout
    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/', AdminDashboard::class)->name('admin.dashboard');
    Route::get('/users', Users::class)->name('admin.users');
    Route::get('/api-keys', ApiKeys::class)->name('admin.api-keys');
    Route::get('/orders', Orders::class)->name('admin.orders');
    Route::get('/payment-methods', PaymentMethods::class)->name('admin.payment-methods');
    Route::get('/subscription-plans', SubscriptionPlans::class)->name('admin.subscription-plans');
});

