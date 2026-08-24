<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GeneratorController;
use App\Http\Controllers\StudentApiController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AdminStudentController;
use Illuminate\Support\Facades\Route;

// 1. Route Publik untuk Halaman Generator & Printer Kartu Pelajar
Route::get('/', [GeneratorController::class, 'index'])->name('home');

// 2. Route Endpoint API untuk AJAX request dari app.js
Route::any('/students_api.php', [StudentApiController::class, 'handle']);

// 3. Route Area Admin (Terproteksi Autentikasi)
Route::middleware(['auth'])->group(function () {
    // Halaman Beranda Dashboard Admin
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // CRUD Manajemen Siswa Admin
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('students', AdminStudentController::class);
    });

    // Profile Admin
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
