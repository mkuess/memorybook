<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportController extends Controller
{
    public function create(): View
    {
        $memoryPages = auth()->user()->memoryPages()->orderBy('person_name')->get();

        return view('support.create', compact('memoryPages'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category'       => ['required', 'in:Problem,Frage,Verbesserungsvorschlag,Sonstiges'],
            'subject'        => ['required', 'string', 'max:200'],
            'description'    => ['required', 'string', 'max:5000'],
            'memory_page_id' => ['nullable', 'integer'],
        ]);

        $memoryPageId = null;
        if (! empty($validated['memory_page_id'])) {
            $page         = auth()->user()->memoryPages()->find($validated['memory_page_id']);
            $memoryPageId = $page?->id;
        }

        Report::create([
            'user_id'        => auth()->id(),
            'memory_page_id' => $memoryPageId,
            'reporter_name'  => auth()->user()->name,
            'reporter_email' => auth()->user()->email,
            'subject'        => $validated['subject'],
            'description'    => $validated['description'],
            'category'       => $validated['category'],
            'status'         => 'open',
        ]);

        return redirect()->route('support.create')
            ->with('success', 'Deine Nachricht wurde gesendet.');
    }
}
