<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\VisitorController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\Auth\RecoveryController;
use App\Http\Controllers\TabSessionController;

// ---------------------------------------------------------
// RECOVERY ROUTES (Secure)
// ---------------------------------------------------------
Route::middleware('guest')->group(function () {
    // Show request form
    Route::get('/forgot-password', [RecoveryController::class, 'showLinkRequestForm'])
        ->name('password.request');

    // Initial request
    Route::post('/forgot-password', [RecoveryController::class, 'sendResetLink'])
        ->name('password.email')
        ->middleware('throttle:5,1');

    // Visitor: Verify code
    Route::get('/verify-code', [RecoveryController::class, 'showVerifyCodeForm'])
        ->name('verify.code');
    Route::post('/verify-code', [RecoveryController::class, 'submitVerifyCode'])
        ->name('verify.code.post')
        ->middleware('throttle:5,1');

    // Visitor: Choice / Reset landing (via email)
    Route::get('/reset-password/{token}', [RecoveryController::class, 'showRecoveryOptions'])
        ->name('password.reset');

    // Group: Direct Recovery Choice (No token)
    Route::get('/recovery-choice', [RecoveryController::class, 'showDirectRecoveryChoice'])
        ->name('recovery.choice');

    // Group: Modify Code ID (Direct)
    Route::get('/modify-code-id', [RecoveryController::class, 'showModifyCodeIdForm'])
        ->name('code.modify');
    Route::post('/modify-code-id', [RecoveryController::class, 'submitCodeIdModification'])
        ->name('code.modify.post')
        ->middleware('throttle:3,10');

    // Group: Identity recovery (Direct)
    Route::get('/recover-identity', [RecoveryController::class, 'showIdentityForm'])
        ->name('identity.recover');
    Route::post('/recover-identity', [RecoveryController::class, 'submitIdentityRecovery'])
        ->name('identity.recover.post')
        ->middleware('throttle:3,10');

    // Final Reset (Visitor)
    Route::post('/reset-password', [RecoveryController::class, 'reset'])
        ->name('password.update')
        ->middleware('throttle:3,10');
});

// ---------------------------------------------------------
// PUBLIC ROUTES
// ---------------------------------------------------------

// Diagnostic Render: bypass web middleware (sessions/cookies/csrf) to debug 500s.
Route::get('/render-diagnose', function () {
    return response()->json([
        'app_env' => config('app.env'),
        'app_debug' => (bool) config('app.debug'),
        'app_url' => config('app.url'),
        'app_key_present' => (bool) config('app.key'),
        'app_key_prefix' => is_string(config('app.key')) ? str_starts_with(config('app.key'), 'base64:') : null,
        'cipher' => config('app.cipher'),
        'php_version' => PHP_VERSION,
        'view_compiled' => config('view.compiled'),
        'storage_writable' => is_writable(storage_path()),
        'bootstrap_cache_writable' => is_writable(base_path('bootstrap/cache')),
        'session_driver' => config('session.driver'),
        'cache_store' => config('cache.default'),
        'db_connection' => config('database.default'),
        'db_host' => config('database.connections.pgsql.host'),
        'db_database' => config('database.connections.pgsql.database'),
    ]);
})->withoutMiddleware([
    \Illuminate\Cookie\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
]);

Route::get('/', function () {
    return view('index');
})->name('home');

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

Route::get('/render-logs', function() {
    $logPath = storage_path('logs/laravel.log');
    if (file_exists($logPath)) {
        return response('<pre>' . htmlspecialchars(file_get_contents($logPath)) . '</pre>');
    }
    return 'Aucun log trouvé.';
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

// Heartbeat : maintient last_activity à jour pour la vérification de session unique
Route::post('/heartbeat', function () {
    return response()->json(['status' => 'ok']);
})->middleware('auth')->name('heartbeat');

// Beacon de fermeture d'onglet
Route::post('/tab-closing', [TabSessionController::class, 'beacon'])->name('tab.closing');

// ---------------------------------------------------------
// PROTECTED ROUTES
// ---------------------------------------------------------

// --- ADMIN ROUTES ---
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    Route::post('/codes', [AdminController::class, 'storeCode'])->name('codes.store');
    Route::delete('/codes/{id}', [AdminController::class, 'destroyCode'])->name('codes.destroy');
    Route::delete('/groups/{id}', [AdminController::class, 'destroyGroup'])->name('groups.destroy');
    Route::delete('/visitors/{id}', [AdminController::class, 'destroyVisitor'])->name('visitors.destroy');
    
    // Admin Events Routes
    Route::post('/events', [AdminController::class, 'storeEvent'])->name('events.store');
    Route::post('/events/{id}', [AdminController::class, 'updateEvent'])->name('events.update');
    Route::post('/events/{id}/media', [AdminController::class, 'uploadEventMedia'])->name('events.updateMedia');
    Route::delete('/events/{id}/media/{type}', [AdminController::class, 'destroyEventMedia'])->name('events.destroyMedia');
    Route::delete('/events/{id}', [AdminController::class, 'destroyEvent'])->name('events.destroy');
});

// --- VISITOR ROUTES ---
Route::middleware(['auth', 'visiteur', 'prevent-back'])->prefix('visiteur')->name('visiteur.')->group(function () {
    Route::get('/', [VisitorController::class, 'index'])->name('dashboard');
    Route::delete('/account', [VisitorController::class, 'deleteAccount'])->name('delete.account');
    
    // Visitor Messaging
    Route::post('/messages/send', [MessageController::class, 'sendMessage'])->name('messages.send');
    Route::get('/messages', [MessageController::class, 'getVisitorMessages'])->name('messages.get');
    Route::post('/messages/read', [MessageController::class, 'markAsRead'])->name('messages.read');
    Route::get('/messages/unread', [MessageController::class, 'unreadCount'])->name('messages.unreadCount');
    Route::delete('/messages/clear', [MessageController::class, 'clearMessages'])->name('messages.clear');
});

// --- GROUP ROUTES ---
Route::middleware(['auth', 'groupe', 'prevent-back'])->prefix('groupe')->name('groupe.')->group(function () {
    Route::get('/', [GroupController::class, 'index'])->name('dashboard');
    Route::post('/upload-video', [GroupController::class, 'uploadVideo'])->name('upload.video');
    Route::delete('/remove-video', [GroupController::class, 'removeVideo'])->name('remove.video');
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
    
    // Self-deletion
    Route::delete('/account', [GroupController::class, 'deleteAccount'])->name('delete.account');
});
Route::get('/render-admin-debug', function() {
    $user = \App\Models\User::where('username', 'admin')->first();
    if (!$user) {
        return response()->json([
            'status' => 'admin_not_found',
            'hint' => 'Visitez /render-admin-force-seed pour créer le compte.'
        ]);
    }
    return response()->json([
        'status' => 'exists',
        'type_role' => $user->type_role,
        'email' => $user->email,
        'password_ok' => \Illuminate\Support\Facades\Hash::check('ITES*cap*ken*L3', $user->password)
    ]);
});

Route::get('/render-admin-force-seed', function() {
    try {
        $user = \App\Models\User::updateOrCreate(
            ['username' => 'admin'],
            [
                'type_role' => 'admin',
                'name' => 'Super Admin',
                'email' => 'admin@admin.com',
                'password' => \Illuminate\Support\Facades\Hash::make('ITES*cap*ken*L3'),
            ]
        );
        return response()->json(['status' => 'success', 'message' => 'Admin account created/updated.', 'user' => $user->username]);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
    }
});

