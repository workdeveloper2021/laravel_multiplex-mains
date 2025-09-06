<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Job extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'jobs';

    protected $fillable = [
        'queue',
        'payload',
        'attempts',
        'reserved_at',
        'available_at',
        'created_at'
    ];

    protected $casts = [
        'payload' => 'array',
        'attempts' => 'integer',
        'reserved_at' => 'datetime',
        'available_at' => 'datetime',
        'created_at' => 'datetime'
    ];
}
