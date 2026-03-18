<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\GoogleAuthController;

use App\Http\Controllers\LoginController;


// មិនទាមទារការ Login
Route::post('/login', [LoginController::class, 'login']);
Route::post('/verify-otp', [LoginController::class, 'verifyOtp']);

// ទាមទារការ Login (មាន Token)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [LoginController::class, 'logout']);
});


Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);


Route::post('/auth/google/token', [GoogleAuthController::class, 'loginWithIdToken']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', fn(Request $request) => $request->user());
    Route::post('logout', function (Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    });
});
//Require verify email before entering dashboard
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->name('dashboard');

Route::post('/forgot-password', [ForgotPasswordController::class, 'store']);
