<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\MemoryPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminGalleryController extends Controller
{
    public const MAX_IMAGES = 5;

    public function store(Request $request, MemoryPage $memoryPage): RedirectResponse
    {
        Gate::allowIf($request->user()->isAdmin());

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
        $filename = Str::uuid() . '.' . strtolower($file->getClientOriginalExtension());
        $dir      = "memory-pages/{$memoryPage->id}/gallery";
        $path     = "{$dir}/{$filename}";

        Storage::disk('public')->putFileAs($dir, $file, $filename);

        [$width, $height] = @getimagesize($file->getPathname()) ?: [null, null];

        $nextSort = ($memoryPage->media()->where('collection', 'gallery')->max('sort_order') ?? -1) + 1;

        $memoryPage->media()->create([
            'collection'        => 'gallery',
            'filename'          => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'path'              => $path,
            'mime_type'         => $file->getMimeType(),
            'size_bytes'        => $file->getSize(),
            'width'             => $width ?: null,
            'height'            => $height ?: null,
            'sort_order'        => $nextSort,
        ]);

        return redirect("/admin/memory-pages/{$memoryPage->id}/edit")
            ->with('gallery_success', 'Bild wurde hochgeladen.');
    }

    public function destroy(MemoryPage $memoryPage, Media $media): RedirectResponse
    {
        Gate::allowIf(request()->user()->isAdmin());

        abort_if(
            $media->memory_page_id !== $memoryPage->id || $media->collection !== 'gallery',
            404
        );

        Storage::disk('public')->delete($media->path);
        $media->delete();

        return redirect("/admin/memory-pages/{$memoryPage->id}/edit")
            ->with('gallery_success', 'Bild wurde entfernt.');
    }
}
