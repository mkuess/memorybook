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
        $filename = 'qrcodes/' . $qrCode->short_code . '.png';

        $builder = new QrCodeBuilder(data: $url, size: 300, margin: 10);
        $writer  = new PngWriter();
        $result  = $writer->write($builder);

        Storage::disk('public')->put($filename, $result->getString());

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
     * Generate a labeled PNG (QR code + "memorybook.com" + short code) on the fly.
     * Returns the raw PNG binary string — not stored to disk.
     */
    public function generateLabeledPng(QrCode $qrCode, string $url): string
    {
        $this->ensureImageExists($qrCode, $url);
        $qrCode->refresh();

        $fontPath = base_path('vendor/endroid/qr-code/assets/open_sans.ttf');
        $domain   = 'memorybook.com';
        $code     = strtoupper($qrCode->short_code);

        $qrContent = Storage::disk('public')->get($qrCode->png_path);
        $qrImage   = imagecreatefromstring($qrContent);
        $qrW       = imagesx($qrImage);
        $qrH       = imagesy($qrImage);

        $domainSize = 14;
        $codeSize   = 18;
        $gapQrText  = 12;
        $gapLines   = 5;
        $paddingBot = 20;

        $domainBox = imagettfbbox($domainSize, 0, $fontPath, $domain);
        $codeBox   = imagettfbbox($codeSize,   0, $fontPath, $code);

        $domainH = abs($domainBox[5] - $domainBox[1]);
        $codeH   = abs($codeBox[5]   - $codeBox[1]);

        $totalH = $qrH + $gapQrText + $domainH + $gapLines + $codeH + $paddingBot;

        $canvas = imagecreatetruecolor($qrW, $totalH);
        $white  = imagecolorallocate($canvas, 255, 255, 255);
        $dark   = imagecolorallocate($canvas, 47, 46, 42);
        imagefill($canvas, 0, 0, $white);

        imagecopy($canvas, $qrImage, 0, 0, 0, 0, $qrW, $qrH);

        $domainW = abs($domainBox[4] - $domainBox[0]);
        $domainX = intval(($qrW - $domainW) / 2);
        $domainY = $qrH + $gapQrText + $domainH;
        imagettftext($canvas, $domainSize, 0, $domainX, $domainY, $dark, $fontPath, $domain);

        $codeW = abs($codeBox[4] - $codeBox[0]);
        $codeX = intval(($qrW - $codeW) / 2);
        $codeY = $domainY + $gapLines + $codeH;
        imagettftext($canvas, $codeSize, 0, $codeX, $codeY, $dark, $fontPath, $code);

        ob_start();
        imagepng($canvas);
        $png = ob_get_clean();

        imagedestroy($qrImage);
        imagedestroy($canvas);

        return $png;
    }
}
