<?php

namespace App\Services;

use App\Models\QrCode;
use Endroid\QrCode\QrCode as QrCodeBuilder;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;

class QrCodeImageService
{
    public function generateAndStore(QrCode $qrCode, string $url): string
    {
        $filename = 'qrcodes/' . strtoupper($qrCode->short_code) . '.png';

        $png = $this->buildBrandedPng($url, $qrCode->short_code);

        Storage::disk('public')->put($filename, $png);

        $qrCode->update(['png_path' => $filename]);

        return $filename;
    }

    public function ensureImageExists(QrCode $qrCode, string $url): void
    {
        if ($qrCode->png_path && Storage::disk('public')->exists($qrCode->png_path)) {
            return;
        }

        $this->generateAndStore($qrCode, $url);
        $qrCode->refresh();
    }

    /**
     * Return the branded PNG bytes for download.
     * The stored file is already the branded version, so we just return it.
     */
    public function generateLabeledPng(QrCode $qrCode, string $url): string
    {
        $this->ensureImageExists($qrCode, $url);
        $qrCode->refresh();

        return Storage::disk('public')->get($qrCode->png_path);
    }

    /**
     * Generate a plain QR-only PNG (no logo, no text) — used for PDF card layouts.
     */
    public function buildRawQrPng(string $url): string
    {
        $builder = new QrCodeBuilder(data: $url, size: 400, margin: 10);
        $writer  = new PngWriter();

        return $writer->write($builder)->getString();
    }

    /**
     * Build the full branded PNG in memory:
     *   [logo symbol]
     *   [QR code]
     *   memorybook.at/
     *   SHORT_CODE
     */
    private function buildBrandedPng(string $url, string $shortCode): string
    {
        $fontPath = base_path('vendor/endroid/qr-code/assets/open_sans.ttf');
        $domain   = 'memorybook.at/';
        $code     = strtoupper($shortCode);

        // --- generate raw QR ---
        $builder  = new QrCodeBuilder(data: $url, size: 360, margin: 10);
        $writer   = new PngWriter();
        $result   = $writer->write($builder);
        $qrImage  = imagecreatefromstring($result->getString());
        $qrW      = imagesx($qrImage);
        $qrH      = imagesy($qrImage);

        // --- load & scale logo ---
        $logoPath     = public_path('images/memorybook-logo.png');
        $logoSrc      = file_exists($logoPath) ? @imagecreatefrompng($logoPath) : null;
        $logoDisplayW = 200;
        $logoDisplayH = $logoSrc
            ? intval(imagesy($logoSrc) / imagesx($logoSrc) * $logoDisplayW)
            : 0;

        // --- layout ---
        $canvasW    = 420;
        $paddingTop = 24;
        $gapLogo    = 20;
        $gapQrText  = 16;
        $gapLines   = 6;
        $paddingBot = 28;

        $domainSize = 14;
        $codeSize   = 18;

        $domainBox = imagettfbbox($domainSize, 0, $fontPath, $domain);
        $codeBox   = imagettfbbox($codeSize,   0, $fontPath, $code);
        $domainH   = abs($domainBox[5] - $domainBox[1]);
        $codeH     = abs($codeBox[5]   - $codeBox[1]);

        $logoBlock = $logoSrc ? ($logoDisplayH + $gapLogo) : 0;
        $totalH    = $paddingTop + $logoBlock + $qrH + $gapQrText + $domainH + $gapLines + $codeH + $paddingBot;

        // --- canvas ---
        $canvas = imagecreatetruecolor($canvasW, $totalH);
        $white  = imagecolorallocate($canvas, 255, 255, 255);
        $dark   = imagecolorallocate($canvas, 47, 46, 42);
        imagefill($canvas, 0, 0, $white);

        // --- draw logo ---
        $y = $paddingTop;
        if ($logoSrc) {
            $logoX = intval(($canvasW - $logoDisplayW) / 2);
            imagecopyresampled(
                $canvas, $logoSrc,
                $logoX, $y, 0, 0,
                $logoDisplayW, $logoDisplayH,
                imagesx($logoSrc), imagesy($logoSrc)
            );
            imagedestroy($logoSrc);
            $y += $logoDisplayH + $gapLogo;
        }

        // --- draw QR code (centered) ---
        $qrX = intval(($canvasW - $qrW) / 2);
        imagecopy($canvas, $qrImage, $qrX, $y, 0, 0, $qrW, $qrH);
        imagedestroy($qrImage);
        $y += $qrH + $gapQrText;

        // --- draw domain ---
        $domainW = abs($domainBox[4] - $domainBox[0]);
        $domainX = intval(($canvasW - $domainW) / 2);
        $y      += $domainH;
        imagettftext($canvas, $domainSize, 0, $domainX, $y, $dark, $fontPath, $domain);

        // --- draw short code ---
        $codeW = abs($codeBox[4] - $codeBox[0]);
        $codeX = intval(($canvasW - $codeW) / 2);
        $y    += $gapLines + $codeH;
        imagettftext($canvas, $codeSize, 0, $codeX, $y, $dark, $fontPath, $code);

        // --- output ---
        ob_start();
        imagepng($canvas);
        $png = ob_get_clean();
        imagedestroy($canvas);

        return $png;
    }
}
