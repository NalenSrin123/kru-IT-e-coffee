<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductSizeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ==========================================
// ១. PUBLIC ROUTES (មិនទាមទារការ Login)
// ==========================================

// --- ក. ប្រព័ន្ធគណនី (Auth) ---
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);
Route::post('/verify-otp', [LoginController::class, 'verifyOtp']);
Route::post('/resend-otp', [LoginController::class, 'resendOtp']);

Route::prefix('auth/google')->group(function () {
    Route::get('/redirect', [GoogleAuthController::class, 'redirectToGoogle']);
    Route::get('/callback', [GoogleAuthController::class, 'handleGoogleCallback']);
    Route::post('/token', [GoogleAuthController::class, 'loginWithIdToken']);
});

// --- ខ. មុខងារសម្រាប់អតិថិជនទូទៅមើលដោយសេរី (API v1 Public) ---
Route::prefix('v1')->group(function () {
    // អនុញ្ញាតឱ្យអ្នកណាក៏អាចហៅ API មើលបញ្ជីប្រភេទ និងផលិតផលបានដែរ (ត្រឹម Index និង Show)
    Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
    Route::apiResource('products', ProductController::class)->only(['index', 'show']);
    Route::get('products/{productId}/sizes', [ProductSizeController::class, 'index']);
});


// ==========================================
// ២. PROTECTED ROUTES (ទាមទារការ Login / Token)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {

    // --- ក. មុខងារគណនីផ្ទាល់ខ្លួន និងចាកចេញ ---
    Route::post('/logout', [LoginController::class, 'logout']);

    Route::middleware('role:Customer')->prefix('v1')->group(function () {
        Route::get('cart/', [CartController::class, 'index']);           // មើលកន្ត្រក
        Route::post('cart/', [CartController::class, 'store']);          // បន្ថែមចូលកន្ត្រក
        Route::put('cart/{itemId}', [CartController::class, 'update']);  // ប្តូរចំនួន (កើន/ថយ)
        Route::delete('cart/{itemId}', [CartController::class, 'destroy']); // លុបចេញពីកន្ត្រក

        // 🌟 Checkout & My Orders
        Route::post('checkout', [OrderController::class, 'checkout']); // បញ្ជាក់ការទិញ
        Route::get('my-orders', [OrderController::class, 'myOrders']); // មើលប្រវត្តិទិញ
    });

    Route::prefix('v1/profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/', [ProfileController::class, 'update']);
        Route::put('/password', [ProfileController::class, 'updatePassword']);
        Route::post('/avatar', [ProfileController::class, 'uploadAvatar']);
    });


    // --- ខ. មុខងារសម្រាប់តែ Super Admin (គ្រប់គ្រងបុគ្គលិក) ---
    Route::middleware('role:Super Admin')->group(function () {
        Route::get('/staff', [UserController::class, 'index']);
        Route::post('/staff', [UserController::class, 'store']);
        Route::get('/staff/{id}', [UserController::class, 'show']);
        Route::put('/staff/{id}', [UserController::class, 'update']);
        Route::delete('/staff/{id}', [UserController::class, 'destroy']);
        Route::post('/staff/{id}/avatar', [UserController::class, 'uploadAvatar']);
    });


    // --- គ. មុខងារទូទៅសម្រាប់ Admin & Super Admin គ្រប់គ្រងទិន្នន័យ (API v1 Protected) ---
    Route::middleware('role:Super Admin|Admin')->prefix('v1')->group(function () {

        // 1. Customers Management (Admin Use)
        Route::apiResource('customers', CustomerController::class)->only(['index', 'show', 'update', 'destroy']);

        // 2. Categories Management (តែ Admin ទេទើបអាច Add, Edit, Delete, Upload រូបភាពបាន)
        Route::get('categories/trashed', [CategoryController::class, 'trashed']);
        Route::post('categories/{id}/restore', [CategoryController::class, 'restore']);
        Route::delete('categories/{id}/force', [CategoryController::class, 'forceDelete']);
        Route::post('categories/{id}/image', [CategoryController::class, 'uploadImage']);
        // 🌟 ប្រើ except ដើម្បីដក index នឹង show ចេញ (ព្រោះយើងដាក់វានៅ Public រួចហើយ)
        Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);

        // 3. Products Management (តែ Admin ទេទើបអាច Add, Edit, Delete, Upload រូបភាពបាន)
        Route::post('products/{id}/image', [ProductController::class, 'uploadImage']);
        // 🌟 ប្រើ except ដើម្បីដក index នឹង show ចេញដូចគ្នា
        Route::apiResource('products', ProductController::class)->except(['index', 'show']);

        // 🌟 4. Product Sizes Management
        Route::post('products/{productId}/sizes', [ProductSizeController::class, 'store']); // បន្ថែមទំហំឱ្យ Product ណាមួយ
        Route::put('sizes/{id}', [ProductSizeController::class, 'update']);                 // កែប្រែតម្លៃ/ទំហំ
        Route::delete('sizes/{id}', [ProductSizeController::class, 'destroy']);             // លុបទំហំចោល

        // 🌟 5. Orders Management
        Route::get('orders', [OrderController::class, 'index']); // ផ្ទាំង Dashboard របស់ Admin មើល Order
        Route::put('orders/{id}/status', [OrderController::class, 'updateStatus']); // Admin ព្រូហ្វ Order
    });
});
