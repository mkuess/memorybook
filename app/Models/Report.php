<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'memory_page_id',
        'reporter_name',
        'reporter_email',
        'subject',
        'category',
        'reason',
        'description',
        'status',
        'admin_notes',
    ];

    protected $attributes = [
        'status'   => 'open',
        'category' => 'profile_report',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function memoryPage(): BelongsTo
    {
        return $this->belongsTo(MemoryPage::class);
    }
}
