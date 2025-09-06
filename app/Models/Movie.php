<?php

namespace App\Models;
use MongoDB\Laravel\Eloquent\Model;

class Movie extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'videos';

    protected $fillable = [
        'title',
        'description',
        'stars',
        'director',
        'writer',
        'rating',
        'country',
        'genre',
        'language',
        'status',
        'video_quality',
        'video_url',
        'video_file',
        'videoContent_id',
        'pricing',
        'channel_id',
        'release',
        'price',
        'is_paid',
        'publication',
        'trailer',
        'trailer_url',
        'thumbnail_url',
        'poster_url',
        'poster_image',
        'enable_download',
        'use_global_price',
        'is_movie',
        'is_channel',
        'is_tvseries',
        'stream_id',
        'download_url',
        'download_link',
        'total_rating',
        'today_view',
        'weekly_view',
        'monthly_view',
        'total_view',
        'last_ep_added',
        'cre',
        'videos_id',
        'created_at',
        'updated_at'
    ];

    // protected $casts = [
    //     'genre' => 'array',
    //     'director' => 'array',
    //     'writer' => 'array',
    //     'country' => 'array',
    //     'language' => 'array',
    //     'pricing' => 'array',
    //     'is_paid' => 'integer', // Store as 0/1 like your DB
    //     'publication' => 'boolean',
    //     'enable_download' => 'string', // Store as '0'/'1' string like your DB
    //     'use_global_price' => 'boolean',
    //     'is_movie' => 'boolean',
    //     'is_channel' => 'boolean',
    //     'is_tvseries' => 'integer', // Store as 0/1
    //     'price' => 'decimal:2',
    //     'total_rating' => 'integer',
    //     'today_view' => 'integer',
    //     'weekly_view' => 'integer',
    //     'monthly_view' => 'integer',
    //     'total_view' => 'integer',
    //     'release' => 'date',
    //     'last_ep_added' => 'datetime',
    //     'cre' => 'datetime',
    //     'created_at' => 'datetime',
    //     'updated_at' => 'datetime'
    // ];

    protected $dates = [
        'release',
        'last_ep_added',
        'cre',
        'created_at',
        'updated_at'
    ];

    // Relationships
    public function channel()
    {
        return $this->belongsTo(Channel::class, 'channel_id', '_id');
    }

    public function videos()
    {
        return $this->belongsTo(Movie::class, 'videos_id', '_id');
    }

    // Scopes
    public function scopePaid($query)
    {
        return $query->where('is_paid', 1);
    }

    public function scopeFree($query)
    {
        return $query->where('is_paid', 0);
    }

    public function scopeMovies($query)
    {
        return $query->where('is_movie', true);
    }

    public function scopeSeries($query)
    {
        return $query->where('is_tvseries', 1);
    }

    public function scopePublished($query)
    {
        return $query->where('publication', true);
    }

    public function scopeChannelContent($query)
    {
        return $query->where('is_channel', true);
    }

    public function scopeAdminContent($query)
    {
        return $query->where('is_channel', false);
    }

    // Accessors
    public function getStreamUrlAttribute()
    {
        if ($this->stream_id) {
            return "https://watch.cloudflarestream.com/{$this->stream_id}";
        }
        return $this->video_url;
    }

    public function getThumbnailAttribute()
    {
        if ($this->stream_id) {
            return "https://videodelivery.net/{$this->stream_id}/thumbnails/thumbnail.jpg";
        }
        return $this->thumbnail_url;
    }

    public function getTrailerStreamAttribute()
    {
        return $this->trailer ?: $this->trailer_url;
    }

    // Check if download is available
    public function isDownloadable()
    {
        return ($this->enable_download === '1' || $this->enable_download === true)
               && ($this->download_url || $this->download_link);
    }

    // Get formatted price
    public function getFormattedPriceAttribute()
    {
        return $this->is_paid ? '₹' . number_format($this->price, 2) : 'Free';
    }

    // Get view stats
    public function getTotalViewsAttribute()
    {
        return $this->total_view ?? 0;
    }

    public function getTodayViewsAttribute()
    {
        return $this->today_view ?? 0;
    }

    public function getWeeklyViewsAttribute()
    {
        return $this->weekly_view ?? 0;
    }

    public function getMonthlyViewsAttribute()
    {
        return $this->monthly_view ?? 0;
    }

    // Rating helpers
    public function getRatingAttribute($value)
    {
        return $value ?: '0';
    }

    public function getStarsAttribute($value)
    {
        return $value ?: '';
    }

    // Increment view counters
    public function incrementViews()
    {
        $this->increment('today_view');
        $this->increment('weekly_view');
        $this->increment('monthly_view');
        $this->increment('total_view');
    }

    // Check if content is from channel
    public function isChannelContent()
    {
        return $this->is_channel === true;
    }

    // Check if content is from admin
    public function isAdminContent()
    {
        return $this->is_channel === false;
    }

    // Get download link
    public function getDownloadLinkAttribute($value)
    {
        return $value ?: $this->download_url;
    }

    // Video quality helper
    public function getVideoQualityAttribute($value)
    {
        return $value ?: 'HD';
    }
}
