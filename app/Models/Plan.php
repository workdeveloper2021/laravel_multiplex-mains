<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;


class Plan extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'plan';
    protected $fillable = [
        'plan_id',
        'name',
        'day',
        'screens',
        'currency',
        'country',
        'price',
        'status'
    ];

}
