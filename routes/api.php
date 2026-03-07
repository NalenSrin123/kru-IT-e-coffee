<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\GoogleAuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/


// ប្រព័ន្ធ Local (Email & Password + OTP)
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);
Route::post('/verify-otp', [LoginController::class, 'verifyOtp']);

// ប្រព័ន្ធ Social (Google Login)
// ប្រើប្រាស់ prefix 'auth/google' ដើម្បីកុំឱ្យសរសេរពាក្យនេះដដែលៗច្រើនដង
Route::prefix('auth/google')->group(function () {
    Route::get('/redirect', [GoogleAuthController::class, 'redirectToGoogle']);
    Route::get('/callback', [GoogleAuthController::class, 'handleGoogleCallback']);
    Route::post('/token', [GoogleAuthController::class, 'loginWithIdToken']);
});


// ទាមទារឱ្យមាន Token (Bearer) ទើបអាចហៅបាន
Route::middleware('auth:sanctum')->group(function () {

    // ទាញយកព័ត៌មានគណនីដែលកំពុង Login
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // ចាកចេញពីប្រព័ន្ធ
    Route::post('/logout', [LoginController::class, 'logout']);
});
