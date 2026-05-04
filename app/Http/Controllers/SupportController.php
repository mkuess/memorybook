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
            'memory_page_id' => [
                'nullable',
                'integer',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value && ! auth()->user()->memoryPages()->where('id', $value)->exists()) {
                        $fail('Diese Erinnerungsseite gehört nicht zu deinem Konto.');
                    }
                },
            ],
        ]);

        $memoryPageId = ! empty($validated['memory_page_id']) ? (int) $validated['memory_page_id'] : null;

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
