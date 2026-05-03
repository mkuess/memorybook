<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class MemoryPage extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::created(function (MemoryPage $page): void {
            $page->qrCode()->create([
                'short_code' => static::generateUniqueShortCode(),
            ]);
        });
    }

    private static function generateUniqueShortCode(): string
    {
        do {
            $code = '';
            for ($i = 0; $i < 8; $i++) {
                $code .= chr(random_int(65, 90));
            }
        } while (QrCode::where('short_code', $code)->exists());

        return $code;
    }

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

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function plaqueOrders(): HasMany
    {
        return $this->hasMany(PlaqueOrder::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }
}
