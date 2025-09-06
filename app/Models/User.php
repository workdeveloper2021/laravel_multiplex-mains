<?php
//
//namespace App\Models;
//
//// use Illuminate\Contracts\Auth\MustVerifyEmail;
////use Illuminate\Database\Eloquent\Factories\HasFactory;
////use MongoDB\Laravel\Auth\User as Authenticatable;
//use MongoDB\Laravel\Eloquent\Model;
//
//
//use Illuminate\Notifications\Notifiable;
//
//class User extends Model
//{
////    use HasFactory, Notifiable;
//
//    protected $connection = 'mongodb';
//    protected $table = 'user'; // 👈 exact collection name in MongoDB
//
//    /**
//     * The attributes that are mass assignable.
//     *
//     * @var array<int, string>
//     */
//    protected $fillable = [
//        'name',
//        'email',
//        'password',
//    ];
//
//    /**
//     * The attributes that should be hidden for serialization.
//     *
//     * @var array<int, string>
//     */
//    protected $hidden = [
//        'password',
//        'remember_token',
//    ];
//
//    /**
//     * Get the attributes that should be cast.
//     *
//     * @return array<string, string>
//     */
//    protected function casts(): array
//    {
//        return [
//            'email_verified_at' => 'datetime',
//            'password' => 'hashed',
//        ];
//    }
//}


namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use MongoDB\Laravel\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use MongoDB\BSON\ObjectId;

class User extends Authenticatable implements AuthenticatableContract
{
    use Notifiable;

    protected $connection = 'mongodb';
    protected $table = 'user';

    protected $fillable = [
        'name',
        'email',
        'password',
        'user_id',
        'channel_id',
        'role',
        'join_date',
        'document_path',
        'profile_image',
        'is_super_admin',
        'admin_level',
        'admin_id',
        'phone',
        'status',
        'watchlist',
        'fcm_token'
    ];

    protected $hidden = [
        // remove 'password' if you want it to show in the response
        'remember_token'
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            // 'password' => 'hashed', ❌ remove this line since you're using md5
        ];
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($user) {
            // Set user_id same as _id when creating
            if (empty($user->user_id)) {
                $user->user_id = new ObjectId();
                $user->_id = $user->user_id;
            }
        });
    }

}
