<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'memory_page_id',
        'short_code',
        'scan_count',
        'png_path',
        'svg_path',
    ];

    protected $attributes = [
        'scan_count' => 0,
    ];

    protected function casts(): array
    {
        return [
            'scan_count' => 'integer',
        ];
    }

    public function memoryPage(): BelongsTo
    {
        return $this->belongsTo(MemoryPage::class);
    }
}
