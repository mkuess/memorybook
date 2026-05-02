<?php

namespace App\Http\Controllers;

use App\Models\MemoryPage;
use Illuminate\View\View;

class PublicMemoryPageController extends Controller
{
    public function show(string $slug): View
    {
        $page = MemoryPage::where('slug', $slug)->first();

        if (! $this->isVisible($page)) {
            return view('memory-pages.unavailable');
        }

        return view('memory-pages.show', compact('page'));
    }

    private function isVisible(?MemoryPage $page): bool
    {
        if ($page === null) {
            return false;
        }

        return $page->is_published
            && ! $page->is_locked
            && in_array($page->visibility, ['link', 'public'], true);
    }
}
