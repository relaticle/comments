# Notifications

> Comment notifications, subscriptions, and real-time updates.

## Notification Types

Two notification classes are included:

### CommentRepliedNotification

Sent to all thread subscribers when a new comment or reply is posted. The comment author is excluded from receiving their own notification.

### UserMentionedNotification

Sent to a user when they are @mentioned in a comment. Self-mentions are ignored.

## Channels

```php
// config/comments.php
'notifications' => [
    'channels' => ['database'],
    'enabled' => true,
],
```

Available channels: `'database'` and `'mail'`. Add both to send email notifications alongside database notifications:

```php
'notifications' => [
    'channels' => ['database', 'mail'],
    'enabled' => true,
],
```

## Subscriptions

Users can subscribe to comment threads on any commentable model. Subscribers receive notifications when new comments are posted.

### Auto-Subscribe

```php
'subscriptions' => [
    'auto_subscribe' => true,
],
```

When enabled:

- Users are auto-subscribed when they post a comment
- Users are auto-subscribed when they are @mentioned

### Manual Subscription

Users can toggle their subscription using the subscribe/unsubscribe button in the comments UI.

### Programmatic Access

```php
use Relaticle\Comments\CommentSubscription;

// Check subscription status
CommentSubscription::isSubscribed($commentable, $user);

// Subscribe/unsubscribe
CommentSubscription::subscribe($commentable, $user);
CommentSubscription::unsubscribe($commentable, $user);

// Get all subscribers for a commentable
$subscribers = CommentSubscription::subscribersFor($commentable);
```

## Events

<table>
<thead>
  <tr>
    <th>
      Event
    </th>
    
    <th>
      Trigger
    </th>
    
    <th>
      Broadcasts
    </th>
  </tr>
</thead>

<tbody>
  <tr>
    <td>
      <code>
        CommentCreated
      </code>
    </td>
    
    <td>
      New comment or reply
    </td>
    
    <td>
      Yes
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        CommentUpdated
      </code>
    </td>
    
    <td>
      Comment edited
    </td>
    
    <td>
      Yes
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        CommentDeleted
      </code>
    </td>
    
    <td>
      Comment soft-deleted
    </td>
    
    <td>
      Yes
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        CommentReacted
      </code>
    </td>
    
    <td>
      Reaction added/removed
    </td>
    
    <td>
      Yes
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        UserMentioned
      </code>
    </td>
    
    <td>
      User @mentioned
    </td>
    
    <td>
      No
    </td>
  </tr>
</tbody>
</table>

## Real-time Updates

### Broadcasting

Enable broadcasting for instant updates across browser sessions:

```php
// config/comments.php
'broadcasting' => [
    'enabled' => true,
    'channel_prefix' => 'comments',
],
```

Events are broadcast on private channels: `{prefix}.{commentable_type}.{commentable_id}`

This requires Laravel Echo and a broadcasting driver (Pusher, Ably, etc.) configured in your application.

### Polling Fallback

When broadcasting is disabled, the Livewire component polls for updates:

```php
'polling' => [
    'interval' => '10s',
],
```

Set to `null` to disable polling entirely.

## Disabling Notifications

```php
'notifications' => [
    'enabled' => false,
],
```

This disables all notification dispatching. Subscriptions and events still work, but no notifications are sent.
