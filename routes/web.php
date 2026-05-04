<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminGalleryController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\MemoryPageController;
use App\Http\Controllers\MemoryPageQrController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfilePhotoController;
use App\Http\Controllers\PublicMemoryPageController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\VisitorMemoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->isAdmin()
            ? redirect('/admin')
            : redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

Route::get('/m/{slug}', [PublicMemoryPageController::class, 'show'])->name('memory-pages.public');

Route::get('/m/{code}/erinnerung-hinterlassen', [VisitorMemoryController::class, 'create'])->name('visitor-memory.create');
Route::post('/m/{code}/erinnerung-hinterlassen', [VisitorMemoryController::class, 'store'])->name('visitor-memory.store')->middleware('throttle:5,1');
Route::get('/m/{code}/erinnerung-hinterlassen/danke', [VisitorMemoryController::class, 'thankYou'])->name('visitor-memory.thankyou');
Route::get('/m/{code}/erinnerung-bestaetigen/{token}', [VisitorMemoryController::class, 'confirm'])->name('visitor-memory.confirm');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/memory-pages/create', [MemoryPageController::class, 'create'])->name('memory-pages.create');
    Route::post('/memory-pages', [MemoryPageController::class, 'store'])->name('memory-pages.store');
    Route::get('/memory-pages/{memoryPage}/edit', [MemoryPageController::class, 'edit'])->name('memory-pages.edit');
    Route::put('/memory-pages/{memoryPage}', [MemoryPageController::class, 'update'])->name('memory-pages.update');
    Route::put('/memory-pages/{memoryPage}/visibility', [MemoryPageController::class, 'updateVisibility'])->name('memory-pages.update-visibility');
    Route::post('/memory-pages/{memoryPage}/publish', [MemoryPageController::class, 'publish'])->name('memory-pages.publish');
    Route::post('/memory-pages/{memoryPage}/unpublish', [MemoryPageController::class, 'unpublish'])->name('memory-pages.unpublish');
    Route::get('/memory-pages/{memoryPage}/remove', [MemoryPageController::class, 'removeConfirm'])->name('memory-pages.remove.confirm');
    Route::post('/memory-pages/{memoryPage}/remove', [MemoryPageController::class, 'remove'])->name('memory-pages.remove');
    Route::get('/memory-pages/{memoryPage}/checkout', [\App\Http\Controllers\CheckoutController::class, 'create'])->name('memory-pages.checkout');
    Route::post('/memory-pages/{memoryPage}/checkout', [\App\Http\Controllers\CheckoutController::class, 'store'])->name('memory-pages.checkout.store');
    Route::get('/memory-pages/{memoryPage}/checkout/confirmed', [\App\Http\Controllers\CheckoutController::class, 'confirmed'])->name('memory-pages.checkout.confirmed');
    Route::get('/memory-pages/{memoryPage}/qr-code', [MemoryPageQrController::class, 'show'])->name('memory-pages.qr-code');
    Route::get('/memory-pages/{memoryPage}/qr-code/download/png', [MemoryPageQrController::class, 'downloadPng'])->name('memory-pages.qr-code.download-png');
    Route::get('/memory-pages/{memoryPage}/qr-code/download/pdf', [MemoryPageQrController::class, 'downloadPdf'])->name('memory-pages.qr-code.download-pdf');
    Route::get('/memory-pages/{memoryPage}/profile-photo/upload', [ProfilePhotoController::class, 'create'])->name('memory-pages.profile-photo.create');
    Route::post('/memory-pages/{memoryPage}/profile-photo', [ProfilePhotoController::class, 'store'])->name('memory-pages.profile-photo.store');
    Route::post('/memory-pages/{memoryPage}/gallery', [GalleryController::class, 'store'])->name('memory-pages.gallery.store');
    Route::delete('/memory-pages/{memoryPage}/gallery/{media}', [GalleryController::class, 'destroy'])->name('memory-pages.gallery.destroy');
    Route::post('/memory-pages/{memoryPage}/admin-gallery', [AdminGalleryController::class, 'store'])->name('memory-pages.admin-gallery.store');
    Route::delete('/memory-pages/{memoryPage}/admin-gallery/{media}', [AdminGalleryController::class, 'destroy'])->name('memory-pages.admin-gallery.destroy');

    Route::get('/memory-pages/{memoryPage}/stories', [StoryController::class, 'index'])->name('memory-pages.stories.index');
    Route::get('/memory-pages/{memoryPage}/stories/create', [StoryController::class, 'create'])->name('memory-pages.stories.create');
    Route::post('/memory-pages/{memoryPage}/stories', [StoryController::class, 'store'])->name('memory-pages.stories.store');
    Route::get('/memory-pages/{memoryPage}/stories/{story}/edit', [StoryController::class, 'edit'])->name('memory-pages.stories.edit');
    Route::put('/memory-pages/{memoryPage}/stories/{story}', [StoryController::class, 'update'])->name('memory-pages.stories.update');
});

Route::get('/admin-qr/{qrCode}/download', function (\App\Models\QrCode $qrCode) {
    abort_if(! auth()->user()?->isAdmin(), 403);

    $service = app(\App\Services\QrCodeImageService::class);
    $url     = route('memory-pages.public', $qrCode->short_code);
    $service->ensureImageExists($qrCode, $url);
    $qrCode->refresh();

    $bytes    = \Illuminate\Support\Facades\Storage::disk('public')->get($qrCode->png_path);
    $filename = 'qrcode-' . strtoupper($qrCode->short_code) . '.png';

    return response($bytes, 200, [
        'Content-Type'        => 'image/png',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
    ]);
})->middleware('auth')->name('admin.qr-codes.download-png');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/support', [SupportController::class, 'create'])->name('support.create');
    Route::post('/support', [SupportController::class, 'store'])->name('support.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
