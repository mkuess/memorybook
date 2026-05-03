<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'memory_page_id',
        'package',
        'status',
        'billing_name',
        'billing_email',
        'billing_address',
        'billing_postal_code',
        'billing_city',
        'billing_country',
        'consent_confirmed_at',
    ];

    protected $attributes = [
        'status'          => 'requested',
        'billing_country' => 'Österreich',
    ];

    protected function casts(): array
    {
        return [
            'consent_confirmed_at' => 'datetime',
        ];
    }

    public static array $packages = [
        'basic'  => 'Erinnerungsseite',
        'plaque' => 'Erinnerungsseite + QR-Plakette',
    ];

    public static array $statuses = [
        'requested'  => 'Angefragt',
        'in_review'  => 'In Prüfung',
        'paid'       => 'Bezahlt',
        'cancelled'  => 'Storniert',
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
