<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemoryPage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'slug',
        'person_name',
        'birth_date',
        'death_date',
        'short_bio',
        'visibility',
        'is_published',
        'is_locked',
        'consent_confirmed_at',
        'published_at',
    ];

    protected $attributes = [
        'visibility'   => 'private',
        'is_published' => false,
        'is_locked'    => false,
    ];

    protected function casts(): array
    {
        return [
            'birth_date'           => 'date',
            'death_date'           => 'date',
            'is_published'         => 'boolean',
            'is_locked'            => 'boolean',
            'consent_confirmed_at' => 'datetime',
            'published_at'         => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function stories(): HasMany
    {
        return $this->hasMany(Story::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    public function qrCode(): HasOne
    {
        return $this->hasOne(QrCode::class);
    }

    public function plaqueOrders(): HasMany
    {
        return $this->hasMany(PlaqueOrder::class);
    }
}
