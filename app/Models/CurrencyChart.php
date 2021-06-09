<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurrencyChart extends Model
{
    use HasFactory;

    const RANGE_DAY = 1;
    const RANGE_DAY_SHORT = 2;
    const RANGE_WEEK = 7;
    const RANGE_MONTH = 30;
    const RANGE_YEAR = 365;
    const RANGE_ALL = 0;

    protected $fillable = [
        'currency_id',
        'range',
        'stats',
    ];
}
