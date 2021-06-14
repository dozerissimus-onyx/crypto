<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HuobiOrder extends Model
{
    use HasFactory;

    const STATUS_CREATED = 'created';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_PARTIAL_FILLED = 'partial-filled';
    const STATUS_FILLED = 'filled';
    const STATUS_PARTIAL_CANCELED = 'partial-canceled';
    const STATUS_CANCELING = 'canceling';
    const STATUS_CANCELED = 'canceled';
    const STATUS_CLOSED = 'closed';

    public $fillable = [
        'order_id',
        'account_id',
        'symbol',
        'quote',
        'amount',
        'type',
        'status'
    ];
}
