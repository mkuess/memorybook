<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'memory_page_id',
        'reporter_name',
        'reporter_email',
        'reason',
        'description',
        'status',
        'admin_notes',
    ];

    protected $attributes = [
        'status' => 'open',
    ];

    public function memoryPage(): BelongsTo
    {
        return $this->belongsTo(MemoryPage::class);
    }
}
