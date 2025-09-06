<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Languages extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'languagesisos';


    protected $fillable = [
        'iso',
        'name',
    ];

}
