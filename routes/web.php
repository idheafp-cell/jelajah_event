<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

// Route untuk menampilkan halaman utama, peta, dan daftar event.

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/peta', [EventController::class, 'map'])->name('events.map');
Route::get('/daftar-event', [EventController::class, 'index'])->name('events.index');

// Route untuk pengguna yang belum login (guest).
Route::middleware('guest')->group(function () {
    // Menampilkan form login.
    Route::get('/login', [AuthController::class, 'create'])->name('login');

    // Memproses email dan password dari form login.
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

// Route untuk pengguna yang sudah login (auth).
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/event/tambah', [EventController::class, 'create'])->name('events.create');
    Route::post('/event', [EventController::class, 'store'])->name('events.store');

// Route untuk admin yang sudah login (auth + admin).
    Route::middleware('admin')->group(function () {
        Route::get('/event/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
        Route::put('/event/{event}', [EventController::class, 'update'])->name('events.update');
        Route::delete('/event/{event}', [EventController::class, 'destroy'])->name('events.destroy');
    });
});
