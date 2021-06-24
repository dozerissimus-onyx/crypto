<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnigmaProduct extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = [
        'product_id',
        'product_name',
        'min_quantity',
        'max_quantity',
    ];
}
