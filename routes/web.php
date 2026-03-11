<?php

use App\Http\Controllers\Auth\UniversalPasswordResetLinkController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::redirect('/dashboard', '/api/dashboard');

Route::post('/forgot-password', [UniversalPasswordResetLinkController::class, 'store'])
    ->middleware(['guest'])
    ->name('password.email');
