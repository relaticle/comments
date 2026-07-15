# Configuration

> Configure threading, reactions, mentions, attachments, notifications, and more.

Publish the configuration file:

```bash
php artisan vendor:publish --tag=comments-config
```

This creates `config/comments.php` with all available options.

## Table Names

```php
'table_names' => [
    'comments' => 'comments',
    'reactions' => 'comment_reactions',
    'mentions' => 'comment_mentions',
    'subscriptions' => 'comment_subscriptions',
    'attachments' => 'comment_attachments',
],
```

Change the table names if they conflict with your application.

## Column Names

```php
'column_names' => [
    'commenter_id' => 'commenter_id',
    'commenter_type' => 'commenter_type',
],
```

## Models

```php
'models' => [
    'comment' => \Relaticle\Comments\Models\Comment::class,
],

'commenter' => [
    'model' => \App\Models\User::class,
],
```

Override the Comment model to add custom behavior. The commenter model defines which class represents the user who comments.

## Policy

```php
'policy' => \Relaticle\Comments\Policies\CommentPolicy::class,
```

See the [Authorization](/essentials/authorization) page for customization details.

## Threading

```php
'threading' => [
    'max_depth' => 2,
],
```

Controls how many levels of nested replies are allowed. A depth of `2` means top-level comments and one level of replies. Set to `1` to disable replies entirely.

## Pagination

```php
'pagination' => [
    'per_page' => 10,
],
```

Number of comments loaded initially and per "Load More" click.

## Reactions

```php
'reactions' => [
    'emoji_set' => [
        'thumbs_up' => "\u{1F44D}",
        'heart' => "\u{2764}\u{FE0F}",
        'celebrate' => "\u{1F389}",
        'laugh' => "\u{1F604}",
        'thinking' => "\u{1F914}",
        'sad' => "\u{1F622}",
    ],
],
```

Customize the available emoji reactions. Keys are used as identifiers in the database, values are the displayed emoji characters.

## Mentions

```php
'mentions' => [
    'enabled' => true,
    'resolver' => \Relaticle\Comments\Mentions\DefaultMentionResolver::class,
    'max_results' => 5,
],
```

The resolver handles searching for users during @mention autocomplete. See the [Mentions](/essentials/mentions) page for creating a custom resolver.

Set `enabled` to `false` to turn off @mention autocomplete in the editor and mention notifications entirely.

## Pinning

```php
'pinning' => [
    'enabled' => true,
    'max_pinned' => 3,
],
```

Controls pinned comments. Set `enabled` to `false` to disable pinning entirely. `max_pinned` limits how many comments can be pinned per thread (`null` for unlimited). See the [Pinning](/essentials/pinning) page for authorization setup.

## Editor Toolbar

```php
'editor' => [
    'toolbar' => [
        ['bold', 'italic', 'strike', 'link'],
        ['bulletList', 'orderedList'],
        ['codeBlock'],
    ],
],
```

Defines which formatting buttons appear in the comment editor. Groups create visual separators in the toolbar.

## Notifications

```php
'notifications' => [
    'channels' => ['database'],
    'enabled' => true,
],
```

Add `'mail'` to the channels array to send email notifications. Set `enabled` to `false` to disable all notifications.

## Subscriptions

```php
'subscriptions' => [
    'auto_subscribe' => true,
],
```

When enabled, users are automatically subscribed to a thread when they create a comment or are mentioned. They receive notifications for subsequent replies.

## Attachments

```php
'attachments' => [
    'enabled' => true,
    'disk' => 'public',
    'max_size' => 10240, // KB
    'allowed_types' => [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'application/pdf',
        'text/plain',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ],
],
```

Controls file upload behavior. Set `enabled` to `false` to remove the attachment UI entirely. The `max_size` is in kilobytes (default 10 MB).

## Broadcasting

```php
'broadcasting' => [
    'enabled' => false,
    'channel_prefix' => 'comments',
],
```

When enabled, comment events are broadcast on private channels using the format `{prefix}.{commentable_type}.{commentable_id}`. Requires Laravel Echo and a broadcasting driver.

## Polling

```php
'polling' => [
    'interval' => '10s',
],
```

When broadcasting is disabled, the Livewire component polls for new comments at this interval. Set to `null` to disable polling.

## Custom User Resolution

Override how the authenticated user is resolved:

```php
use Relaticle\Comments\CommentsConfig;

// In AppServiceProvider::boot()
CommentsConfig::resolveAuthenticatedUserUsing(function () {
    return auth()->user();
});
```

This is useful for multi-guard applications or custom authentication flows.
