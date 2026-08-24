<?php

use App\Http\Controllers\GeneratorController;
use App\Http\Controllers\StudentApiController;
use Illuminate\Support\Facades\Route;

// 1. Halaman Utama Pencetak Kartu Pelajar (Publik)
Route::get('/', [GeneratorController::class, 'index'])->name('home');

// 2. Endpoint API untuk AJAX request dari app.js (Publik)
Route::any('/students_api.php', [StudentApiController::class, 'handle']);

// Catatan: Seluruh rute dashboard admin, login, dan CRUD dikelola secara otomatis oleh FilamentPHP di bawah URL '/admin'
