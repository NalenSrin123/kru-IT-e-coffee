<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\ProductController;
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

Route::prefix('v1')->group(function () {

    
    Route::get('categories/trashed',        [CategoryController::class, 'trashed']);
    Route::post('categories/{id}/restore',  [CategoryController::class, 'restore']);
    Route::delete('categories/{id}/force',  [CategoryController::class, 'forceDelete']);

    Route::apiResource('categories', CategoryController::class);

});

// Product Routes
Route::prefix('v1')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    Route::apiResource('products', ProductController::class);
});