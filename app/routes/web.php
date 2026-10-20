<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\GuestController;
use App\Http\Controllers\InvitationController;
use Illuminate\Support\Facades\Route;

// La raíz no revela nada: las invitaciones solo se abren con su código personal.
Route::get('/', fn () => abort(404));

Route::prefix('i/{guest:code}')->group(function () {
    Route::get('/', [InvitationController::class, 'show'])
        ->middleware('throttle:60,1')
        ->name('invitation.show');

    Route::post('/rsvp', [InvitationController::class, 'rsvp'])
        ->middleware('throttle:20,1')
        ->name('invitation.rsvp');
});

// Panel de los novios (usuario único y compartido).
Route::prefix('admin')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('admin.login');

    Route::middleware('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('admin.logout');

        Route::get('/', [GuestController::class, 'index'])->name('admin.index');
        Route::get('export', [GuestController::class, 'export'])->name('admin.export');
        Route::post('import', [GuestController::class, 'import'])->name('admin.import');

        Route::get('guests/create', [GuestController::class, 'create'])->name('admin.guests.create');
        Route::post('guests', [GuestController::class, 'store'])->name('admin.guests.store');
        Route::get('guests/{guest:code}/edit', [GuestController::class, 'edit'])->name('admin.guests.edit');
        Route::put('guests/{guest:code}', [GuestController::class, 'update'])->name('admin.guests.update');
        Route::post('guests/{guest:code}/reset', [GuestController::class, 'reset'])->name('admin.guests.reset');
        Route::delete('guests/{guest:code}', [GuestController::class, 'destroy'])->name('admin.guests.destroy');
    });
});
