<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Country extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'currency';
    protected $fillable = [
        'country',
        'currency',
        'symbol',
        'iso_code',
        'exchange_rate',
        'default',
        'status',
    ];
}
