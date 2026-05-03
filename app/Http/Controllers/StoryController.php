<?php

namespace App\Http\Controllers;

use App\Models\MemoryPage;
use App\Models\Story;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class StoryController extends Controller
{
    public function index(Request $request, MemoryPage $memoryPage): View
    {
        Gate::allowIf($request->user()->id === $memoryPage->user_id);

        $stories = $memoryPage->stories()->orderBy('sort_order')->orderBy('created_at')->get();

        return view('stories.index', compact('memoryPage', 'stories'));
    }

    public function create(Request $request, MemoryPage $memoryPage): View
    {
        Gate::allowIf($request->user()->id === $memoryPage->user_id);

        return view('stories.create', compact('memoryPage'));
    }

    public function store(Request $request, MemoryPage $memoryPage): RedirectResponse
    {
        Gate::allowIf($request->user()->id === $memoryPage->user_id);

        $validated = $request->validate([
            'content'      => ['required', 'string'],
            'image'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_published' => ['boolean'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('story-images', 'public');
        }

        $memoryPage->stories()->create([
            'user_id'      => $request->user()->id,
            'title'        => 'Erinnerung vom ' . now()->format('d.m.Y'),
            'content'      => $validated['content'],
            'image_path'   => $imagePath,
            'is_published' => $validated['is_published'] ?? false,
        ]);

        return redirect()
            ->route('memory-pages.stories.index', $memoryPage)
            ->with('success', 'Erinnerung gespeichert.');
    }

    public function edit(Request $request, MemoryPage $memoryPage, Story $story): View
    {
        Gate::allowIf($request->user()->id === $memoryPage->user_id);

        abort_unless($story->memory_page_id === $memoryPage->id, 404);

        return view('stories.edit', compact('memoryPage', 'story'));
    }

    public function update(Request $request, MemoryPage $memoryPage, Story $story): RedirectResponse
    {
        Gate::allowIf($request->user()->id === $memoryPage->user_id);

        abort_unless($story->memory_page_id === $memoryPage->id, 404);

        $validated = $request->validate([
            'content'      => ['required', 'string'],
            'image'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_published' => ['boolean'],
        ]);

        $imagePath = $story->image_path;
        if ($request->hasFile('image')) {
            if ($story->image_path) {
                Storage::disk('public')->delete($story->image_path);
            }
            $imagePath = $request->file('image')->store('story-images', 'public');
        }

        $story->update([
            'content'      => $validated['content'],
            'image_path'   => $imagePath,
            'is_published' => $validated['is_published'] ?? false,
        ]);

        return redirect()
            ->route('memory-pages.stories.index', $memoryPage)
            ->with('success', 'Erinnerung aktualisiert.');
    }
}
