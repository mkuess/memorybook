<?php

namespace App\Http\Controllers;

use App\Models\MemoryPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ProfilePhotoController extends Controller
{
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

        // Remove old profile photo
        $existing = $memoryPage->media()->where('collection', 'profile')->first();
        if ($existing) {
            Storage::disk('public')->delete($existing->path);
            $existing->delete();
        }

        // Store new file
        Storage::disk('public')->putFileAs($directory, $file, $filename);

        // Image dimensions (best-effort)
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

        return back()->with('success', 'Profilfoto gespeichert.');
    }
}
