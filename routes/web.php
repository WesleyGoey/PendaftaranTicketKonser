<?php

use App\Http\Controllers\ConcertController;
use App\Http\Controllers\RegistrationController;
use App\Models\Concert;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! Concert::where('artist', 'Bruno Mars')->exists()) {
        Concert::create([
            'title' => 'Bruno Mars Live in Surabaya',
            'artist' => 'Bruno Mars',
            'concert_date' => '2027-02-09 18:00:00',
            'venue' => 'Universitas Ciputra Surabaya, Indonesia',
            'total_tickets' => 100,
            'available_tickets' => 100,
            'price' => 1500000,
            'description' => 'A spectacular night of Bruno Mars hits, soulful vocals, and unforgettable live performances.',
        ]);
    }

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
