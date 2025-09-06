<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Episodes extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'episodes';

    protected $fillable = [
        '_id',
        'title',
        'video_url',
        'videoContent_id',
        'seasonId',
        'thumbnail_url',
        'enable_download',
        'download_url',
        'channel_id',
        'duration',
        'episode_number',
        'createdAt',
        'updatedAt',
        'description',
        'genre',
        'release_year',
        'image_url',
    ];

    protected $casts = [
        'enable_download' => 'string',
        'episode_number' => 'string',
        'duration' => 'integer',
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
    ];

    // Disable automatic timestamps since we're managing them manually
    public $timestamps = false;

}
