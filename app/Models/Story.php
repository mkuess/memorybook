<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Story extends Model
{
    use HasFactory;

    protected $fillable = [
        'memory_page_id',
        'user_id',
        'title',
        'content',
        'image_path',
        'visitor_email',
        'visitor_token',
        'visitor_token_expires_at',
        'is_visitor_submission',
        'sort_order',
        'is_published',
    ];

    protected $attributes = [
        'sort_order'            => 0,
        'is_published'          => false,
        'is_visitor_submission' => false,
    ];

    protected function casts(): array
    {
        return [
            'sort_order'               => 'integer',
            'is_published'             => 'boolean',
            'is_visitor_submission'    => 'boolean',
            'visitor_token_expires_at' => 'datetime',
        ];
    }

    public function memoryPage(): BelongsTo
    {
        return $this->belongsTo(MemoryPage::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
