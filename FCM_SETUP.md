# Firebase Cloud Messaging (FCM) Push Notifications Setup

This implementation adds FCM push notifications with three main flows:
1. **Manual Push** - Admin-only notifications
2. **Admin Upload** - Automatic notifications when admins upload content  
3. **Channel Upload** - Automatic notifications to subscribers when channels upload content

## Installation

1. **Install Dependencies**
   ```bash
   composer install
   ```

2. **Firebase Setup**
   - Create a Firebase project at https://console.firebase.google.com
   - Generate a service account key (JSON file) from Project Settings > Service Accounts
   - Place the JSON file in `storage/app/firebase-credentials.json`

3. **Environment Configuration**
   Add these variables to your `.env` file:
   ```env
   FCM_PROJECT_ID=your-firebase-project-id
   FCM_CREDENTIALS_PATH=/absolute/path/to/storage/app/firebase-credentials.json
   ```

4. **Queue Configuration**
   Ensure your queue is configured and running:
   ```bash
   # Start queue worker
   php artisan queue:work
   ```

## API Endpoints

### 1. Manual Push Notification (Admin Only)
**POST** `/api/notifications/manual`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "title": "Breaking News",
  "body": "Check out our latest movie releases!",
  "image": "https://example.com/image.jpg",
  "target": "all|channel|users",
  "channel_id": "68a33e4ea65e6746cd06019b", 
  "user_ids": ["68a33aa0640aa2a582f211b5", "..."]
}
```

**Target Types:**
- `all` - Send to all users with FCM tokens
- `channel` - Send to all subscribers of specified channel (requires `channel_id`)
- `users` - Send to specific users (requires `user_ids` array)

### 2. Update FCM Token
**POST** `/api/user/fcm-token`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "fcm_token": "user_device_fcm_token_here"
}
```

## Automatic Notifications

### Admin Upload Flow
When a user with `admin` role or `is_super_admin=true` uploads content:
- Sends notification to **all users** with valid FCM tokens
- Message: "New {movie|episode} added - {title} is now available."

### Channel Upload Flow  
When a user with `channel` role uploads content:
- Gets all subscribers from `ChannelSubs` collection for that channel
- Filters users who have valid FCM tokens
- Sends notification only to those subscribers
- Message: "New {movie|episode} added - {title} is now available."

## Database Schema

### Users Collection
The `fcm_token` field has been added to the User model:
```php
protected $fillable = [
    // ... existing fields
    'fcm_token'
];
```

### ChannelSubs Collection
Uses existing structure:
```json
{
  "_id": "...",
  "channel": "68a33e4ea65e6746cd06019b",
  "user": "68a33aa0640aa2a582f211b5",
  "created_at": "...",
  "__v": 0
}
```

## Configuration

### FCM Settings (`config/fcm.php`)
- `batch_size`: 500 (FCM limit)
- Default notification settings for Android/iOS

### Queue Configuration
Notifications are processed via `BuildAndDispatchFcmMessages` job:
- 3 retry attempts
- Exponential backoff: 60s, 120s, 300s
- Logs success/failure counts

## Logging

All notification events are logged with details:
- **Manual notifications**: Admin ID, target type, token count
- **Automated notifications**: Content type, channel ID, success/failure counts
- **Errors**: Failed tokens, API errors, job failures

## Testing

1. **Update User FCM Token**
   ```bash
   curl -X POST http://your-app.com/api/user/fcm-token \
     -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Content-Type: application/json" \
     -d '{"fcm_token":"device_fcm_token_here"}'
   ```

2. **Send Manual Notification**
   ```bash
   curl -X POST http://your-app.com/api/notifications/manual \
     -H "Authorization: Bearer ADMIN_TOKEN" \
     -H "Content-Type: application/json" \
     -d '{
       "title":"Test Notification",
       "body":"This is a test message",
       "target":"all"
     }'
   ```

3. **Upload Content** (triggers automatic notifications)
   - Upload a movie/episode as admin → notifies all users
   - Upload a movie/episode as channel → notifies channel subscribers

## Security Notes

- Only admins can send manual notifications
- FCM tokens are filtered for null/empty values automatically
- Invalid tokens are logged for cleanup
- Firebase credentials should never be committed to version control

## Troubleshooting

1. **No notifications received:**
   - Check queue worker is running: `php artisan queue:work`
   - Verify Firebase credentials path and permissions
   - Check logs for failed job attempts

2. **Permission errors:**
   - Ensure service account has FCM send permissions
   - Verify project ID matches Firebase console

3. **Token issues:**
   - Invalid tokens are automatically logged
   - Users need to re-register FCM tokens periodically
