<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\GiftCard.
 *
 * @property int $id
 * @property string|null $country
 * @property string $code
 * @property string $provider
 * @property int $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $currency_id
 * @property-read \App\Models\Currency|null $currency
 * @property-read string|null $formatted_created_at
 * @property-read string $formatted_provider
 * @property-read \App\Models\Transaction|null $transaction
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GiftCard newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GiftCard newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GiftCard query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GiftCard whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GiftCard whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GiftCard whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GiftCard whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GiftCard whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GiftCard whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GiftCard whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GiftCard whereValue($value)
 * @mixin \Eloquent
 */
class GiftCard extends Model
{
    use HasFactory;

    const PROVIDER_APPLE = 'apple';
    const PROVIDER_XBOX = 'xbox';
    const PROVIDER_ROBLOX = 'roblox';
    const PROVIDER_PSN = 'psn';
    const PROVIDER_GOOGLE_PLAY = 'google_play';
    const PROVIDER_NETFLIX = 'netflix';
    const PROVIDER_SPOTIFY = 'spotify';
    const PROVIDER_DISCORD = 'discord';
    const PROVIDER_STEAM = 'steam';
    const PROVIDER_FORTNITE = 'fortnite';
    const PROVIDER_VALORANT = 'valorant';

    const PROVIDERS = [
        self::PROVIDER_APPLE,
        self::PROVIDER_XBOX,
        self::PROVIDER_ROBLOX,
        self::PROVIDER_PSN,
        self::PROVIDER_GOOGLE_PLAY,
        self::PROVIDER_NETFLIX,
        self::PROVIDER_SPOTIFY,
        self::PROVIDER_DISCORD,
        self::PROVIDER_STEAM,
        self::PROVIDER_FORTNITE,
        self::PROVIDER_VALORANT,
    ];

    protected $fillable = [
        'country',
        'code',
        'provider',
        'value',
        'currency_id',
    ];

    protected $appends = [
        'formatted_created_at',
        'formatted_provider',
    ];

    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function getFormattedCreatedAtAttribute(): ?string
    {
        return optional($this->created_at)->format('M d Y');
    }

    public function getFormattedProviderAttribute(): string
    {
        if (in_array($this->provider, [self::PROVIDER_XBOX, self::PROVIDER_PSN])) {
            return strtoupper($this->provider);
        }

        return str_replace('_', ' ', ucwords($this->provider, '_'));
    }
}
