<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'memory_page_id',
        'collection',
        'filename',
        'original_filename',
        'path',
        'mime_type',
        'size_bytes',
        'width',
        'height',
        'caption',
        'sort_order',
    ];

    protected $attributes = [
        'sort_order' => 0,
    ];

    protected function casts(): array
    {
        return [
            'size_bytes'  => 'integer',
            'width'       => 'integer',
            'height'      => 'integer',
            'sort_order'  => 'integer',
        ];
    }

    public function memoryPage(): BelongsTo
    {
        return $this->belongsTo(MemoryPage::class);
    }
}
