<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Seasons extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'seasons';
    protected $fillable = [
        'title',
        'webSeries',
        'episodesId'
    ];
}
