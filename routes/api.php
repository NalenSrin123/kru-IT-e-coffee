<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\UserController; // 🌟 កុំភ្លេច Import UserController មកផង

use App\Http\Controllers\ProductController;
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

    Route::get('/customers', [CustomerController::class, 'getAllCustomer']);
    Route::put('/customers/{id}', [CustomerController::class, 'updateCustomer']);
    Route::delete('/customers/{id}', [CustomerController::class, 'deleteCustomer']);

    // ចាកចេញពីប្រព័ន្ធ
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
<<<<<<< HEAD

Route::get('categories/{category}/products', [ProductController::class, 'byCategory']); 
=======
>>>>>>> ef6167b9f80cb110867d9058a2abd5d2b53d12f9
