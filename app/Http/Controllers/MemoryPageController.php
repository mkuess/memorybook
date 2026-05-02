<?php

namespace App\Http\Controllers;

use App\Models\MemoryPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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

    public function edit(MemoryPage $memoryPage): View
    {
        Gate::allowIf(auth()->id() === $memoryPage->user_id);

        $profilePhoto = $memoryPage->media()->where('collection', 'profile')->first();

        return view('memory-pages.edit', compact('memoryPage', 'profilePhoto'));
    }

    public function update(Request $request, MemoryPage $memoryPage): RedirectResponse
    {
        Gate::allowIf(auth()->id() === $memoryPage->user_id);

        $validated = $request->validate([
            'person_name' => ['required', 'string', 'max:255'],
            'birth_date'  => ['nullable', 'date'],
            'death_date'  => ['nullable', 'date'],
            'short_bio'   => ['nullable', 'string'],
            'visibility'  => ['required', 'string', 'in:private,link,public'],
        ]);

        $memoryPage->update($validated);

        return redirect()->route('dashboard');
    }

    public function publish(Request $request, MemoryPage $memoryPage): RedirectResponse
    {
        Gate::allowIf(auth()->id() === $memoryPage->user_id);

        $request->validate([
            'consent' => ['accepted'],
        ]);

        $memoryPage->update([
            'is_published'         => true,
            'consent_confirmed_at' => $memoryPage->consent_confirmed_at ?? now(),
            'published_at'         => now(),
        ]);

        return redirect()->route('memory-pages.edit', $memoryPage);
    }

    public function unpublish(MemoryPage $memoryPage): RedirectResponse
    {
        Gate::allowIf(auth()->id() === $memoryPage->user_id);

        $memoryPage->update([
            'is_published' => false,
            'published_at' => null,
        ]);

        return redirect()->route('memory-pages.edit', $memoryPage);
    }

    private function generateUniqueSlug(): string
    {
        do {
            $slug = strtolower(Str::random(8));
        } while (MemoryPage::where('slug', $slug)->exists());

        return $slug;
    }
}
