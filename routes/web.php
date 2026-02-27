<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\VisitorController;
use App\Http\Controllers\GroupController;

// ---------------------------------------------------------
// PUBLIC ROUTES
// ---------------------------------------------------------

Route::get('/', function () {
    return view('index');
});

// Authentication Views
Route::get('/login', function () {
    return view('login');
})->name('login');

Route::get('/inscription', function () {
    return view('inscription');
})->name('inscription');

Route::get('/admin_login', function () {
    return view('admin_login');
})->name('admin.login');

// Authentication Processing
Route::post('/login', [AuthController::class, 'login'])->name('auth.login.post');
Route::post('/inscription/visiteur', [AuthController::class, 'registerVisitor'])->name('auth.register.visitor');
Route::post('/inscription/groupe', [AuthController::class, 'registerGroup'])->name('auth.register.group');
Route::post('/admin_login', [AdminAuthController::class, 'login'])->name('admin.login.post');

// Logout (Requires Auth)
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ---------------------------------------------------------
// PROTECTED ROUTES
// ---------------------------------------------------------

// --- ADMIN ROUTES ---
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    Route::post('/codes', [AdminController::class, 'storeCode'])->name('codes.store');
    Route::delete('/codes/{id}', [AdminController::class, 'destroyCode'])->name('codes.destroy');
    Route::delete('/groups/{id}', [AdminController::class, 'destroyGroup'])->name('groups.destroy');
});

// --- VISITOR ROUTES ---
Route::middleware(['auth', 'visiteur'])->prefix('visiteur')->name('visiteur.')->group(function () {
    Route::get('/', [VisitorController::class, 'index'])->name('dashboard');
});

// --- GROUP ROUTES ---
Route::middleware(['auth', 'groupe'])->prefix('groupe')->name('groupe.')->group(function () {
    Route::get('/', [GroupController::class, 'index'])->name('dashboard');
});
