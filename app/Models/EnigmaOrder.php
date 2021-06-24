<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnigmaOrder extends Model
{
    use HasFactory;

    const STATUS_CREATED = 'created';
    const STATUS_REPEATED = 'repeated';
    const STATUS_BOOKED = 'booked';
    const STATUS_VALIDATED = 'validated';
    const STATUS_CANCELED = 'canceled';
    const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'order_id',
        'type',
        'product_id',
        'product_name',
        'user_id',
        'message',
        'side',
        'quantity',
        'price',
        'nominal',
        'status'
    ];
}
