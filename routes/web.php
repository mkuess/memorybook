<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MemoryPageController;
use App\Http\Controllers\MemoryPageQrController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicMemoryPageController;
use App\Http\Controllers\StoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/m/{slug}', [PublicMemoryPageController::class, 'show'])->name('memory-pages.public');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/memory-pages/create', [MemoryPageController::class, 'create'])->name('memory-pages.create');
    Route::post('/memory-pages', [MemoryPageController::class, 'store'])->name('memory-pages.store');
    Route::get('/memory-pages/{memoryPage}/edit', [MemoryPageController::class, 'edit'])->name('memory-pages.edit');
    Route::put('/memory-pages/{memoryPage}', [MemoryPageController::class, 'update'])->name('memory-pages.update');
    Route::post('/memory-pages/{memoryPage}/publish', [MemoryPageController::class, 'publish'])->name('memory-pages.publish');
    Route::post('/memory-pages/{memoryPage}/unpublish', [MemoryPageController::class, 'unpublish'])->name('memory-pages.unpublish');
    Route::get('/memory-pages/{memoryPage}/qr-code', [MemoryPageQrController::class, 'show'])->name('memory-pages.qr-code');

    Route::get('/memory-pages/{memoryPage}/stories', [StoryController::class, 'index'])->name('memory-pages.stories.index');
    Route::get('/memory-pages/{memoryPage}/stories/create', [StoryController::class, 'create'])->name('memory-pages.stories.create');
    Route::post('/memory-pages/{memoryPage}/stories', [StoryController::class, 'store'])->name('memory-pages.stories.store');
    Route::get('/memory-pages/{memoryPage}/stories/{story}/edit', [StoryController::class, 'edit'])->name('memory-pages.stories.edit');
    Route::put('/memory-pages/{memoryPage}/stories/{story}', [StoryController::class, 'update'])->name('memory-pages.stories.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
