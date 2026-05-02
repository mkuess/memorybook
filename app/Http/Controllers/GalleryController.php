<?php

namespace App\Http\Controllers;

use App\Models\MemoryPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GalleryController extends Controller
{
    public const MAX_IMAGES = 5;

    public function store(Request $request, MemoryPage $memoryPage): RedirectResponse
    {
        Gate::allowIf(auth()->id() === $memoryPage->user_id);

        $request->validate([
            'image' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        $existing = $memoryPage->media()->where('collection', 'gallery')->count();

        if ($existing >= self::MAX_IMAGES) {
            return back()->withErrors(['image' => 'Maximal 5 Galeriebilder möglich.']);
        }

        $file     = $request->file('image');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $dir      = "memory-pages/{$memoryPage->id}/gallery";
        $path     = "{$dir}/{$filename}";

        Storage::disk('public')->putFileAs($dir, $file, $filename);

        $width  = null;
        $height = null;
        $info   = @getimagesize($file->getPathname());
        if ($info !== false) {
            $width  = $info[0];
            $height = $info[1];
        }

        $nextSort = ($memoryPage->media()->where('collection', 'gallery')->max('sort_order') ?? -1) + 1;

        $memoryPage->media()->create([
            'collection'        => 'gallery',
            'filename'          => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'path'              => $path,
            'mime_type'         => $file->getMimeType(),
            'size_bytes'        => $file->getSize(),
            'width'             => $width,
            'height'            => $height,
            'sort_order'        => $nextSort,
        ]);

        return back()->with('gallery_success', 'Bild wurde hochgeladen.');
    }
}
