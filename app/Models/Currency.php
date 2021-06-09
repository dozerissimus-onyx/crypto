<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Currency
 * @property $id
 * @property $type
 */

class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'price',
        'ranking',
        '24h_change',
        '24h_volume',
        'circulating_supply',
        'market_cap'
    ];

    public function charts() {
        return $this->hasMany(CurrencyChart::class);
    }
}
