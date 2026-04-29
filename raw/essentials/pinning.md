# Pinned Comments

> Pin important comments to the top of a thread.

## How Pinning Works

Pinned comments appear in a dedicated section above the regular comment list. Only top-level comments (not replies) can be pinned.

When a comment is pinned it is excluded from the regular sorted list so it never appears twice.

## Setup

Pinning is enabled by default but **nobody can pin** until you configure authorization.

### Step 1 — Authorize who can pin

Register a callback in your `AppServiceProvider::boot()`:

```php
use Relaticle\Comments\CommentsConfig;

CommentsConfig::authorizePinUsing(fn ($user, $comment) => $user->is_admin);
```

The callback receives the authenticated user and the `Comment` model. Return `true` to allow.

Any logic works:

```php
// Role-based (e.g. Spatie Permission)
CommentsConfig::authorizePinUsing(
    fn ($user, $comment) => $user->hasRole('moderator')
);

// Comment owner can pin their own
CommentsConfig::authorizePinUsing(
    fn ($user, $comment) => $user->getKey() === $comment->commenter_id
);
```

### Step 2 (optional) — Config

```php
// config/comments.php
'pinning' => [
    'enabled' => true,
    'max_pinned' => 3,   // null = unlimited
],
```

## Alternative: Custom Policy

Instead of a callback you can override the policy:

```php
// App/Policies/CustomCommentPolicy.php
public function pin(Authenticatable $user, Comment $comment): bool
{
    return $user->is_admin;
}
```

Register it in `config/comments.php`:

```php
'policy' => App\Policies\CustomCommentPolicy::class,
```

The `authorizePinUsing` callback takes priority over the policy when both are set.

## Events

<table>
<thead>
  <tr>
    <th>
      Event
    </th>
    
    <th>
      Fired when
    </th>
  </tr>
</thead>

<tbody>
  <tr>
    <td>
      <code>
        CommentPinned
      </code>
    </td>
    
    <td>
      A comment is pinned
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        CommentUnpinned
      </code>
    </td>
    
    <td>
      A comment is unpinned
    </td>
  </tr>
</tbody>
</table>

Listen to them in your `EventServiceProvider` like any other Laravel event.

## Configuration Reference

<table>
<thead>
  <tr>
    <th>
      Key
    </th>
    
    <th>
      Default
    </th>
    
    <th>
      Description
    </th>
  </tr>
</thead>

<tbody>
  <tr>
    <td>
      <code>
        pinning.enabled
      </code>
    </td>
    
    <td>
      <code>
        true
      </code>
    </td>
    
    <td>
      Enable/disable pinning entirely
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        pinning.max_pinned
      </code>
    </td>
    
    <td>
      <code>
        3
      </code>
    </td>
    
    <td>
      Max pinned comments per thread (<code>
        null
      </code>
      
       = unlimited)
    </td>
  </tr>
</tbody>
</table>
