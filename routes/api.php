<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\UserController; // 🌟 កុំភ្លេច Import UserController មកផង

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ==========================================
// ១. Public Routes (មិនទាមទារ Login)
// ==========================================
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);
Route::post('/verify-otp', [LoginController::class, 'verifyOtp']);
Route::post('/resend-otp', [LoginController::class, 'resendOtp']);

Route::prefix('auth/google')->group(function () {
    Route::get('/redirect', [GoogleAuthController::class, 'redirectToGoogle']);
    Route::get('/callback', [GoogleAuthController::class, 'handleGoogleCallback']);
    Route::post('/token', [GoogleAuthController::class, 'loginWithIdToken']);
});


// ==========================================
// ២. Protected Routes (ទាមទារ Token)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {

    // មុខងារទូទៅសម្រាប់អ្នក Login រួច
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        $user->load('role'); // ភ្ជាប់ Role ទៅឱ្យ Frontend ស្រួលឆែកសិទ្ធិ
        return $user;
    });
    Route::post('/logout', [LoginController::class, 'logout']);

    // ------------------------------------------
    // 🌟 មុខងារគ្រប់គ្រងបុគ្គលិក (Staff CRUD)
    // អនុញ្ញាតឱ្យចូលបានតែ 'Super Admin' ប៉ុណ្ណោះ
    // ------------------------------------------
    Route::middleware('role:Super Admin')->group(function () {
        Route::get('/staff', [UserController::class, 'index']);
        Route::post('/staff', [UserController::class, 'store']);
        Route::get('/staff/{id}', [UserController::class, 'show']);
        Route::put('/staff/{id}', [UserController::class, 'update']);
        Route::delete('/staff/{id}', [UserController::class, 'destroy']);

        // មុខងារ Upload រូបភាព (ប្រើ Method POST ព្រោះជា multipart/form-data)
        Route::post('/staff/{id}/avatar', [UserController::class, 'uploadAvatar']);
    });
});
