<?php

namespace App\Http\Controllers;

use App\Models\QrCode;
use Illuminate\View\View;

class PublicMemoryPageController extends Controller
{
    public function show(string $code): View
    {
        $qr = QrCode::where('short_code', $code)->first();

        if ($qr === null) {
            return view('memory-pages.unavailable');
        }

        $page = $qr->memoryPage;

        if ($page === null) {
            return view('memory-pages.unavailable');
        }

        $user              = auth()->user();
        $isOwner           = $user && $user->id === $page->user_id;
        $isAdmin           = $user && $user->isAdmin();
        $isPubliclyVisible = $this->isVisible($page);

        if ($isAdmin) {
            $previewMode = 'admin';
        } elseif ($isOwner && ! $page->is_locked) {
            $previewMode = $isPubliclyVisible ? null : 'owner';
        } elseif ($isPubliclyVisible) {
            $previewMode = null;
        } else {
            return view('memory-pages.unavailable');
        }

        if ($previewMode === null) {
            $qr->increment('scan_count');
        }

        $stories = $page->stories()
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->get();

        $profilePhoto  = $page->media()->where('collection', 'profile')->first();
        $galleryImages = $page->media()
            ->where('collection', 'gallery')
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->get();

        $canLeaveMemory = ($previewMode === null) && $page->canBePublished();
        $shortCode      = $code;

        return view('memory-pages.show', compact('page', 'stories', 'profilePhoto', 'galleryImages', 'previewMode', 'canLeaveMemory', 'shortCode'));
    }

    private function isVisible($page): bool
    {
        if ($page === null) {
            return false;
        }

        return $page->is_published
            && ! $page->is_locked
            && in_array($page->visibility, ['link', 'public'], true);
    }
}
