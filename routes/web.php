<?php

use App\Http\Controllers\Calendar\AdventCalendarController;
use App\Http\Controllers\Calendar\CalendarDayController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::inertia('dashboard', 'Dashboard')
        ->name('dashboard')
        ->middleware(EnsureUserIsAdmin::class);

    Route::get('calendar', [AdventCalendarController::class, 'index'])->name('calendar.index');
    Route::post('calendar/days/{day}/solve', [AdventCalendarController::class, 'solve'])
        ->name('calendar.days.solve');

    Route::middleware(EnsureUserIsAdmin::class)->group(function (): void {
        Route::get('calendar/edit', [CalendarDayController::class, 'index'])->name('calendar.edit');
        Route::get('calendar/days/{day}/edit', [CalendarDayController::class, 'edit'])->name('calendar.days.edit');
        Route::patch('calendar/days/{day}', [CalendarDayController::class, 'update'])->name('calendar.days.update');
    });
});

require __DIR__.'/settings.php';
