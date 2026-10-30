<?php

use App\Http\Controllers\ConcertController;
use App\Http\Controllers\RegistrationController;
use App\Models\Concert;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $concert = Concert::where('artist', 'Bruno Mars')
        ->where('available_tickets', '>', 0)
        ->orderBy('concert_date')
        ->first();

    return view('welcome', compact('concert'));
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

// Concert routes
Route::get('/concerts', [ConcertController::class, 'index'])->name('concerts.index');
Route::get('/concerts/{concert}', [ConcertController::class, 'show'])->name('concerts.show');
Route::post('/concerts/purchase', [RegistrationController::class, 'store'])
    ->name('concerts.store');

require __DIR__.'/settings.php';
