<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConcertController;

Route::inertia('/', 'concerts/Index')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

// Concert routes
Route::get('/concerts', [ConcertController::class, 'index'])->name('concerts.index');
Route::get('/concerts/{concert}', [ConcertController::class, 'show'])->name('concerts.show');
Route::post('/concerts/purchase', [ConcertController::class, 'store'])->name('concerts.store');

require __DIR__.'/settings.php';
