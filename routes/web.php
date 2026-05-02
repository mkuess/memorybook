<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MemoryPageController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/memory-pages/create', [MemoryPageController::class, 'create'])->name('memory-pages.create');
    Route::post('/memory-pages', [MemoryPageController::class, 'store'])->name('memory-pages.store');
    Route::get('/memory-pages/{memoryPage}/edit', [MemoryPageController::class, 'edit'])->name('memory-pages.edit');
    Route::put('/memory-pages/{memoryPage}', [MemoryPageController::class, 'update'])->name('memory-pages.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
