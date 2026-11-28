<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConcertController;
use App\Http\Controllers\EventsController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ConcertController::class, 'home'])->name('home');

// Daftar konser dihapus; alamat lama diarahkan ke halaman utama.
Route::get('/concerts', fn () => redirect('/'));
Route::get('/concerts/{concert}', [ConcertController::class, 'show'])->whereNumber('concert')->name('concerts.show');

// Server-Sent Events untuk sisa tiket. Tanpa sesi/cookie: datanya publik.
Route::get('/events', EventsController::class)->withoutMiddleware('web')->name('events');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1')->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/concerts/purchase', [RegistrationController::class, 'store'])->name('concerts.store');
    Route::get('/my-tickets', [RegistrationController::class, 'index'])->name('tickets.index');
});
