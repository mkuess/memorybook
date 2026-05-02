<?php

namespace App\Http\Controllers;

use App\Models\MemoryPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MemoryPageController extends Controller
{
    public function create(): View
    {
        return view('memory-pages.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'person_name' => ['required', 'string', 'max:255'],
            'birth_date'  => ['nullable', 'date'],
            'death_date'  => ['nullable', 'date'],
            'short_bio'   => ['nullable', 'string'],
        ]);

        $validated['user_id'] = auth()->id();
        $validated['slug']    = $this->generateUniqueSlug();

        MemoryPage::create($validated);

        return redirect()->route('dashboard');
    }

    private function generateUniqueSlug(): string
    {
        do {
            $slug = strtolower(Str::random(8));
        } while (MemoryPage::where('slug', $slug)->exists());

        return $slug;
    }
}
