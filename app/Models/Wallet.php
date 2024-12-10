<?php

namespace App\Models;

use App\Enums\Currency;
use Database\Factories\WalletFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Wallet extends Model
{
    /** @use HasFactory<WalletFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'currency',
        'balance',
    ];

    /**
     * The model's attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'currency' => Currency::USD,
        'balance' => 0,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'currency' => Currency::class,
            'balance' => 'integer',
        ];
    }

    /**
     * Get or set the balance attribute.
     */
    public function balance(): Attribute
    {
        return Attribute::make(
            get: fn(int $value) => $value / $this->currency->getSubunit(),
            set: fn(float $value) => $value * $this->currency->getSubunit(),
        );
    }

    /**
     * Get the user that owns the wallet.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
