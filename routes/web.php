<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\GuestInviteController;
use App\Http\Controllers\PublicGuestController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/guests');
});

// Public, token-gated guest self-registration (reached via a QR code / link).
Route::get('/g/{invite}', [PublicGuestController::class, 'create'])->name('public.guests.create');
Route::post('/g/{invite}', [PublicGuestController::class, 'store'])->name('public.guests.store');

// Guest-only routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Staff-facing management of the QR invite links.
    Route::get('/invites', [GuestInviteController::class, 'index'])->name('invites.index');
    Route::post('/invites', [GuestInviteController::class, 'store'])->name('invites.store');
    Route::get('/invites/{invite}', [GuestInviteController::class, 'show'])->name('invites.show');
    Route::get('/invites/{invite}/download', [GuestInviteController::class, 'download'])->name('invites.download');
    Route::patch('/invites/{invite}/toggle', [GuestInviteController::class, 'toggle'])->name('invites.toggle');
    Route::delete('/invites/{invite}', [GuestInviteController::class, 'destroy'])->name('invites.destroy');

    Route::resource('guests', GuestController::class)->except('show');
});
