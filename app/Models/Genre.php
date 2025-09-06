<?php
namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Genre extends Model{


    protected $connection = 'mongodb';
    protected $table = 'genre'; // MongoDB collection name

    protected $fillable = [
        'name',
        'description',
        'publication',
        'featured',
        'image_url',
        'url'
    ];


    protected $hidden = [
        'remember_token'
    ];

}
