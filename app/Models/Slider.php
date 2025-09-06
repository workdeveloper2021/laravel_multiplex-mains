<?php
namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Slider extends Model{


    protected $connection = 'mongodb';
    protected $table = 'slider'; // MongoDB collection name

    protected $fillable = [
        'slider_id',
        'title',
        'description',
        'videos_id',
        'image_link',
        'slug',
        'action_type',
        'action_btn_text',
        'action_id',
        'action_url',
        'order',
        'publication',
    ];


    protected $hidden = [
        'remember_token'
    ];

}
