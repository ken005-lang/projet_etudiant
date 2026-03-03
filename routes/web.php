<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\VisitorController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\MessageController;

// ---------------------------------------------------------
// PUBLIC ROUTES
// ---------------------------------------------------------

Route::get('/', function () {
    return view('index');
});

// Temporary debug route - remove after diagnosis
Route::get('/php-debug', function () {
    return response()->json([
        'php_ini_file' => php_ini_loaded_file(),
        'scanned_files' => php_ini_scanned_files(),
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'max_execution_time' => ini_get('max_execution_time'),
    ]);
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
    
    // Admin Events Routes
    Route::post('/events', [AdminController::class, 'storeEvent'])->name('events.store');
    Route::post('/events/{id}', [AdminController::class, 'updateEvent'])->name('events.update');
    Route::post('/events/{id}/media', [AdminController::class, 'uploadEventMedia'])->name('events.updateMedia');
    Route::delete('/events/{id}', [AdminController::class, 'destroyEvent'])->name('events.destroy');
});

// --- VISITOR ROUTES ---
Route::middleware(['auth', 'visiteur'])->prefix('visiteur')->name('visiteur.')->group(function () {
    Route::get('/', [VisitorController::class, 'index'])->name('dashboard');
    
    // Visitor Messaging
    Route::post('/messages/send', [MessageController::class, 'sendMessage'])->name('messages.send');
    Route::get('/messages', [MessageController::class, 'getVisitorMessages'])->name('messages.get');
    Route::post('/messages/read', [MessageController::class, 'markAsRead'])->name('messages.read');
    Route::get('/messages/unread', [MessageController::class, 'unreadCount'])->name('messages.unreadCount');
    Route::delete('/messages/clear', [MessageController::class, 'clearMessages'])->name('messages.clear');
});

// --- GROUP ROUTES ---
Route::middleware(['auth', 'groupe'])->prefix('groupe')->name('groupe.')->group(function () {
    Route::get('/', [GroupController::class, 'index'])->name('dashboard');
    Route::post('/upload-video', [GroupController::class, 'uploadVideo'])->name('upload.video');
    Route::post('/upload-reports', [GroupController::class, 'uploadReports'])->name('upload.reports');
    Route::delete('/reports/{id}', [GroupController::class, 'deleteReport'])->name('delete.report');
    
    // Auto-save API routes
    Route::post('/update-profile', [GroupController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/members', [GroupController::class, 'addMember'])->name('addMember');
    Route::delete('/members/{id}', [GroupController::class, 'removeMember'])->name('removeMember');
    Route::post('/upload-image', [GroupController::class, 'uploadImage'])->name('uploadImage');
    
    // Group Messaging
    Route::post('/messages/{id}/reply', [MessageController::class, 'replyMessage'])->name('messages.reply');
    Route::get('/messages', [MessageController::class, 'getGroupMessages'])->name('messages.get');
    Route::post('/messages/read', [MessageController::class, 'markAsRead'])->name('messages.read');
    Route::get('/messages/unread', [MessageController::class, 'unreadCount'])->name('messages.unreadCount');
    Route::delete('/messages/clear', [MessageController::class, 'clearMessages'])->name('messages.clear');
});
