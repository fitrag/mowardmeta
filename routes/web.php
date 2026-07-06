<?php

use App\Http\Controllers\SocialiteController;
use App\Livewire\Admin\ApiKeys;
use App\Livewire\Admin\AppSettings;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\Licenses;
use App\Livewire\Admin\LicenseOrders;
use App\Livewire\Admin\LicensePlans;
use App\Livewire\Admin\Orders;
use App\Livewire\Admin\PaymentMethods;
use App\Livewire\Admin\Products;
use App\Livewire\Admin\ProductOrders;
use App\Livewire\Admin\SubscriptionPlans;
use App\Livewire\Admin\Users;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\User\Dashboard;
use App\Livewire\User\History;
use App\Livewire\User\KeywordGenerator;
use App\Livewire\User\LicenseStore;
use App\Livewire\User\MetadataGenerator;
use App\Livewire\User\ProductStore;
use App\Livewire\User\Settings;
use App\Livewire\User\Subscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/

// Symlink route for shared hosting (run once then remove/comment)
Route::get('/symlink', function () {
    // Path ke folder storage/app/public di core Laravel
    // Sesuaikan path ini dengan struktur hosting kamu
    $target = base_path('../mowardmeta/storage/app/public');
    
    // Path symlink di folder public
    $link = base_path('../meta.mowardstudio.com/storage');
    
    // Jika sudah ada, hapus dulu
    if (is_link($link)) {
        unlink($link);
    }
    
    // Buat symlink
    if (symlink($target, $link)) {
        return 'Symlink created successfully!<br>Target: ' . $target . '<br>Link: ' . $link;
    }
    
    return 'Failed to create symlink. Please check folder permissions.';
});

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
    Route::get('/licenses', LicenseStore::class)->name('licenses');
    Route::get('/products', ProductStore::class)->name('products');
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
    Route::get('/licenses', Licenses::class)->name('admin.licenses');
    Route::get('/license-plans', LicensePlans::class)->name('admin.license-plans');
    Route::get('/license-orders', LicenseOrders::class)->name('admin.license-orders');
    Route::get('/products', Products::class)->name('admin.products');
    Route::get('/product-orders', ProductOrders::class)->name('admin.product-orders');
    Route::get('/settings', AppSettings::class)->name('admin.settings');
});

