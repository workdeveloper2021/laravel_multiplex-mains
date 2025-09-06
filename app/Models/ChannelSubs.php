<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\BSON\ObjectId;
use App\Models\User;
class ChannelSubs extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'subcribes';
    protected $fillable = [
        'channel',
        'user',
        'creeated_at'
    ];
    
    protected $dates = ['creeated_at'];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class, 'user', '_id');
    }
    
    // Relationship with Channel  
    public function channel()
    {
        return $this->belongsTo(Channel::class, 'channel', '_id');
    }



}
