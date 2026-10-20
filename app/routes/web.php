<?php

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
