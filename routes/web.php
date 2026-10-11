<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AccountSettingsController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\MitraController;
use App\Http\Controllers\ProductImageController;
use Illuminate\Support\Facades\Route;

// ==== Public ====
Route::get('/',            [HomeController::class, 'index'])->name('home');
Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace');
Route::get('/media/products/{filename}', [ProductImageController::class, 'show'])
    ->where('filename', '[A-Za-z0-9._-]+')
    ->name('media.product-image');
Route::get('/booking',     [BookingController::class, 'create'])->name('booking');
Route::post('/booking',    [BookingController::class, 'store'])->name('booking.store');
Route::get('/mitra',       [MitraController::class, 'index'])->name('mitra.index');
Route::post('/mitra',      [MitraController::class, 'store'])->name('mitra.store');
Route::get('/cs',          [CsController::class, 'index'])->name('cs');
Route::post('/cs',         [CsController::class, 'store'])->name('cs.store');

// ==== Auth ====
Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',   [AuthController::class, 'login'])->name('login.attempt');
Route::post('/register',[AuthController::class, 'register'])->name('register');
Route::post('/logout',  [AuthController::class, 'logout'])->name('logout');

// ==== Marketplace ====
Route::middleware('camrent.auth')->group(function () {
    Route::post('/marketplace/sell',          [MarketplaceController::class, 'sell'])->name('marketplace.sell');
});
Route::post('/marketplace/buy/{product}', [MarketplaceController::class, 'buy'])->name('marketplace.buy');

// Dashboard can show the current guest session or the signed-in account.
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// ==== Dashboard actions (butuh login) ====
Route::middleware('camrent.auth')->group(function () {
    Route::get('/pengaturan-akun', [AccountSettingsController::class, 'edit'])->name('account.settings');
    Route::patch('/pengaturan-akun', [AccountSettingsController::class, 'update'])->name('account.settings.update');
    Route::get('/media/avatars/{filename}', [ProductImageController::class, 'showAvatar'])
        ->where('filename', '[A-Za-z0-9._-]+')
        ->name('media.account-avatar');
    Route::get('/dashboard/booking/{booking}',  [DashboardController::class, 'bookingDetail'])->name('dashboard.booking.detail');
    Route::patch('/dashboard/booking/{booking}/status/{status}', [DashboardController::class, 'updateOrderStatus'])->name('dashboard.booking.status');
});