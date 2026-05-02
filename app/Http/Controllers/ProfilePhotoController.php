<?php

namespace App\Http\Controllers;

use App\Models\MemoryPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfilePhotoController extends Controller
{
    public function create(Request $request, MemoryPage $memoryPage): View
    {
        Gate::allowIf(
            $request->user()->id === $memoryPage->user_id
            || $request->user()->isAdmin()
        );

        $from         = $request->query('from', 'customer');
        $profilePhoto = $memoryPage->media()->where('collection', 'profile')->first();

        return view('memory-pages.profile-photo-upload', [
            'memoryPage'   => $memoryPage,
            'from'         => $from,
            'profilePhoto' => $profilePhoto,
        ]);
    }

    public function store(Request $request, MemoryPage $memoryPage): RedirectResponse
    {
        Gate::allowIf(
            $request->user()->id === $memoryPage->user_id
            || $request->user()->isAdmin()
        );

        $request->validate([
            'photo' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $file      = $request->file('photo');
        $directory = "memory-pages/{$memoryPage->id}/profile";
        $extension = strtolower($file->getClientOriginalExtension());
        $filename  = uniqid('', true) . '.' . $extension;
        $path      = "{$directory}/{$filename}";

        $existing = $memoryPage->media()->where('collection', 'profile')->first();
        if ($existing) {
            Storage::disk('public')->delete($existing->path);
            $existing->delete();
        }

        Storage::disk('public')->putFileAs($directory, $file, $filename);

        [$width, $height] = @getimagesize($file->getPathname()) ?: [null, null];

        $memoryPage->media()->create([
            'collection'        => 'profile',
            'filename'          => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'path'              => $path,
            'mime_type'         => $file->getMimeType(),
            'size_bytes'        => $file->getSize(),
            'width'             => $width ?: null,
            'height'            => $height ?: null,
            'sort_order'        => 0,
        ]);

        if ($request->input('from') === 'admin') {
            return redirect("/admin/memory-pages/{$memoryPage->id}/edit")
                ->with('success', 'Profilfoto gespeichert.');
        }

        return back()->with('success', 'Profilfoto gespeichert.');
    }
}
