<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HuobiSymbol extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = [
        'base_currency',
        'quote_currency',
        'min_order_amount',
        'max_order_amount',
    ];
}
