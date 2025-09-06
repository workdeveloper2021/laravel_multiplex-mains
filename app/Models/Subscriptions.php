<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Subscriptions extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'subscription';

    protected $fillable = [
        '_id',
        'user_id',
        'plan_id',
        'channel_id',
        'video_id',
        'price_amount',
        'timestamp_from',
        'timestamp_to',
        'payment_method',
        'payment_info',
        'recurring',
        'status',
        'ispayment',
        'receipt',
        'razorpay_order_id',
        'currency',
        'amount',
        'amount_due',
        'amount_paid',
        'created_at',
        'is_active',
        '__v',
        'assigned_by',
        'assigned_at',
        'api_response'
    ];

    protected $casts = [
        // 'timestamp_from' => 'integer',
        // 'timestamp_to' => 'integer',
        // 'payment_info' => 'array',
        // 'recurring' => 'integer',
        // 'status' => 'integer',
        // 'ispayment' => 'integer',
        // 'price_amount' => 'float',
        // 'amount' => 'float',
        // 'amount_due' => 'float',
        // 'amount_paid' => 'float',
        // 'created_at' => 'integer',
        // 'is_active' => 'boolean',
        // '__v' => 'integer',
        // 'api_response' => 'array',
        // 'assigned_at' => 'datetime',
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
     * Relationship with Plan
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id', '_id');
    }

    /**
     * Relationship with Channel
     */
    public function channel()
    {
        return $this->belongsTo(Channel::class, 'channel_id', '_id');
    }

    /**
     * Relationship with assigned by user
     */
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by', '_id');
    }
}
