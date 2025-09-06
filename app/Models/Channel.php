<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\BSON\ObjectId;
use App\Models\User;
class Channel extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'channel';
    protected $fillable = [
        'channel_name',
        'channel_img',
        'user_id',
        'mobile_number',
        'address',
        'email',
        'organization_name',
        'organization_address',
        'document_path',
        'status',
        'join_date',
        'last_login',
        'img'
    ];

//    public function user()
//    {
//        return $this->belongsTo(User::class, 'user_id', '_id');
//    }

    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'pending' => '⏳ Pending Approval',
            'approve' => '✅ Approved',
            'rejected' => '❌ Rejected',
            'block' => '🚫 Blocked',
            default => '⚠️ Unknown',
        };
    }


    public function getUserAttribute()
    {
        try {
            return User::find(new ObjectId($this->user_id));
        } catch (\Exception $e) {
            return null;
        }
    }



}
