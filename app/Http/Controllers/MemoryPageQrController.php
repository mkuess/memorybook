<?php

namespace App\Http\Controllers;

use App\Models\MemoryPage;
use App\Services\QrCodeImageService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

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

    public function downloadPng(Request $request, MemoryPage $memoryPage): Response
    {
        Gate::allowIf($request->user()->id === $memoryPage->user_id);

        $qr  = $memoryPage->qrCode;
        $url = route('memory-pages.public', $qr->short_code);
        $png = $this->qrImageService->generateLabeledPng($qr, $url);

        $filename = 'qrcode-' . strtoupper($qr->short_code) . '.png';

        return response($png, 200, [
            'Content-Type'        => 'image/png',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length'      => strlen($png),
        ]);
    }

    public function downloadPdf(Request $request, MemoryPage $memoryPage): \Illuminate\Http\Response
    {
        Gate::allowIf($request->user()->id === $memoryPage->user_id);

        $qr  = $memoryPage->qrCode;
        $url = route('memory-pages.public', $qr->short_code);

        $rawQrB64 = base64_encode($this->qrImageService->buildRawQrPng($url));
        $logoPath = public_path('images/memorybook-logo.png');
        $logoB64  = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : '';
        $code     = strtoupper($qr->short_code);

        $pdf = Pdf::loadView('memory-pages.qr-download-pdf', compact('rawQrB64', 'logoB64', 'code'))
                  ->setPaper('A4', 'portrait');

        $filename = 'qrcode-' . $code . '.pdf';

        return $pdf->download($filename);
    }
}
