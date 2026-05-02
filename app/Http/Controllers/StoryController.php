<?php

namespace App\Http\Controllers;

use App\Models\MemoryPage;
use App\Models\Story;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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
            'title'        => ['required', 'string', 'max:255'],
            'content'      => ['required', 'string'],
            'is_published' => ['boolean'],
        ]);

        $memoryPage->stories()->create([
            'user_id'      => $request->user()->id,
            'title'        => $validated['title'],
            'content'      => $validated['content'],
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
            'title'        => ['required', 'string', 'max:255'],
            'content'      => ['required', 'string'],
            'is_published' => ['boolean'],
        ]);

        $story->update([
            'title'        => $validated['title'],
            'content'      => $validated['content'],
            'is_published' => $validated['is_published'] ?? false,
        ]);

        return redirect()
            ->route('memory-pages.stories.index', $memoryPage)
            ->with('success', 'Erinnerung aktualisiert.');
    }
}
