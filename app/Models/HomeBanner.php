<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class HomeBanner extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'slider';

    protected $fillable = [
        'title',
        'description',
        'image_url',
        'banner_url',
        'video_url',
        'url',
        'cta_url',
        'cta_text',
        'order',
        'publication',
    ];
}
