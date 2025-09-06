<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Payment extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'payments';

    protected $fillable = [
        '_id',
        'user_id',
        'channel_id',
        'video_id', 
        'plan_id',
        'price_amount',
        'paid_amount',
        'custom_duration',
        'api_response',
        'status',
        'assigned_by',
        'assigned_at',
        'createdAt',
        'updatedAt',
    ];

    protected $casts = [
        'price_amount' => 'float',
        'paid_amount' => 'float',
        'custom_duration' => 'integer',
        'api_response' => 'array',
        'assigned_at' => 'datetime',
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
    ];

    // Disable automatic timestamps since we're managing them manually
    public $timestamps = false;

    /**
     * Relationship with User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', '_id');
    }

    /**
     * Relationship with Channel
     */
    public function channel()
    {
        return $this->belongsTo(Channel::class, 'channel_id', '_id');
    }

    /**
     * Relationship with Plan
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id', '_id');
    }

    /**
     * Relationship with assigned by user
     */
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by', '_id');
    }
}
