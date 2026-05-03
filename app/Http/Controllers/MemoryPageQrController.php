<?php

namespace App\Http\Controllers;

use App\Models\MemoryPage;
use App\Services\QrCodeImageService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Gate;

class MemoryPageQrController extends Controller
{
    public function __construct(private QrCodeImageService $qrImageService) {}

    public function show(Request $request, MemoryPage $memoryPage): View
    {
        Gate::allowIf($request->user()->id === $memoryPage->user_id);

        $qr  = $memoryPage->qrCode;
        $url = route('memory-pages.public', $qr->short_code);

        $this->qrImageService->ensureImageExists($qr, $url);

        return view('memory-pages.qr-code', compact('memoryPage', 'qr'));
    }
}
