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
}
