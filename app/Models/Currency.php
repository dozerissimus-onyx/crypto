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
    public static $exampleAddresses = [
        'BTC' => '3LoJFcGiBgCzy235poxmq8uZGFGSK3ZbJN',
        'ETH' => '0x501906Ce564be7bA80Eb55A29EE31ECfaE41b6f2',
        'USDT' => '0x501906Ce564be7bA80Eb55A29EE31ECfaE41b6f2',
        'XRP' => 'rhVWrjB9EGDeK4zuJ1x2KXSjjSpsDQSaU6',
        'BCH' => 'bitcoincash:qpsvdqxu2f4thh5sq5nepx28p0swtv9zkquv748lh7',
        'LTC' => 'MT3ACvgp53eWcBtJV7j1Hj6Xfz4hb2UDRT',
        'OBT' => '',
        'USDC' => '0xF4042bcD4Ac25E8865a996463C66759329a36B4F',
        'EOS' => '21xnkedexi0oywyojp1vvlzjmbjdkcag',
        'COMP' => '0x09f0F5035f9633c58b3493D4C4334291E643B262',
        'LINK' => '0x501906Ce564be7bA80Eb55A29EE31ECfaE41b6f2',
        'XLM' => 'GCXDBCRQHDTUJDSZUJPC5TTLBERIWRC7SYBTZO3UOFM2QBE2JXK3DJKE',
        'XTZ' => 'tz2X6L6KDL7mSyUxV7dFYsukr5TSLyXp6WRx',
        'ZEC' => 't1KnAa91REAFRsHoSRx9nr9y2pyux8ZaTXG',
        'GRT' => '0xc944E90C64B2c07662A292be6244BDf05Cda44a7',
        'YFI' => '0x09f0F5035f9633c58b3493D4C4334291E643B262'
    ];

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
