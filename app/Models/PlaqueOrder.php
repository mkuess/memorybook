<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaqueOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'memory_page_id',
        'user_id',
        'status',
        'contact_name',
        'contact_email',
        'contact_phone',
        'shipping_address',
        'notes',
        'admin_notes',
    ];

    protected $attributes = [
        'status' => 'requested',
    ];

    public function memoryPage(): BelongsTo
    {
        return $this->belongsTo(MemoryPage::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
