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

        $qr->increment('scan_count');

        $page = $qr->memoryPage;

        if (! $this->isVisible($page)) {
            return view('memory-pages.unavailable');
        }

        return view('memory-pages.show', compact('page'));
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
