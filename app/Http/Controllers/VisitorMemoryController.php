<?php

namespace App\Http\Controllers;

use App\Mail\VisitorMemoryConfirmationMail;
use App\Models\MemoryPage;
use App\Models\QrCode;
use App\Models\Story;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class VisitorMemoryController extends Controller
{
    public function create(string $code): View
    {
        $page = $this->resolveEligiblePage($code);

        return view('visitor-memory.create', compact('page', 'code'));
    }

    public function store(Request $request, string $code): RedirectResponse
    {
        $page = $this->resolveEligiblePage($code);

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:5000'],
            'image'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'email'   => ['required', 'email', 'max:255'],
            'consent' => ['accepted'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('story-images', 'public');
        }

        $token = Str::random(64);

        $story = $page->stories()->create([
            'user_id'                  => $page->user_id,
            'title'                    => 'Erinnerung vom ' . now()->format('d.m.Y'),
            'content'                  => $validated['content'],
            'image_path'               => $imagePath,
            'visitor_email'            => $validated['email'],
            'visitor_token'            => $token,
            'visitor_token_expires_at' => now()->addDays(7),
            'is_visitor_submission'    => true,
            'is_published'             => false,
        ]);

        try {
            Mail::to($validated['email'])->send(new VisitorMemoryConfirmationMail($story, $code));
        } catch (\Throwable $e) {
            Log::warning('Could not send visitor memory confirmation email.', [
                'story_id' => $story->id,
                'error'    => $e->getMessage(),
            ]);
        }

        return redirect()->route('visitor-memory.thankyou', $code);
    }

    public function thankYou(string $code): View
    {
        $qr = QrCode::where('short_code', $code)->first();
        abort_if(! $qr || ! $qr->memoryPage, 404);

        return view('visitor-memory.thankyou');
    }

    public function confirm(string $code, string $token): View
    {
        $story = Story::where('visitor_token', $token)
            ->where('is_visitor_submission', true)
            ->where('is_published', false)
            ->first();

        if (! $story || ($story->visitor_token_expires_at && $story->visitor_token_expires_at->isPast())) {
            return view('visitor-memory.expired');
        }

        $story->update([
            'is_published'  => true,
            'visitor_token' => null,
        ]);

        return view('visitor-memory.confirmed');
    }

    private function resolveEligiblePage(string $code): MemoryPage
    {
        $qr = QrCode::where('short_code', $code)->first();
        abort_if(! $qr || ! $qr->memoryPage, 404);

        $page = $qr->memoryPage;

        $isVisible = $page->is_published
            && ! $page->is_locked
            && in_array($page->visibility, ['link', 'public'], true);

        abort_if(! $isVisible || ! $page->canBePublished(), 403);

        return $page;
    }
}
