<?php

use App\Http\Controllers\Calendar\AdventCalendarController;
use App\Http\Controllers\Calendar\CalendarDayController;
use App\Http\Controllers\PlayerController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// The welcome page is gone: guests go straight to the login screen,
// logged-in users land on their calendar.
Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('calendar.index')
        : redirect()->route('login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::inertia('dashboard', 'Dashboard')
        ->name('dashboard')
        ->middleware(EnsureUserIsAdmin::class);

    Route::get('calendar', [AdventCalendarController::class, 'index'])->name('calendar.index');
    Route::post('calendar/days/{day}/solve', [AdventCalendarController::class, 'solve'])
        ->name('calendar.days.solve');

    Route::middleware(EnsureUserIsAdmin::class)->group(function (): void {
        Route::get('players', [PlayerController::class, 'index'])->name('players.index');
        Route::post('players', [PlayerController::class, 'store'])->name('players.store');

        Route::get('calendar/edit', [CalendarDayController::class, 'index'])->name('calendar.edit');
        Route::get('calendar/days/{day}/edit', [CalendarDayController::class, 'edit'])->name('calendar.days.edit');
        Route::patch('calendar/days/{day}', [CalendarDayController::class, 'update'])->name('calendar.days.update');
    });
});

require __DIR__.'/settings.php';
