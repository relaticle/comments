# Mentions

> User @mentions with autocomplete and notification support.

## How Mentions Work

Type `@` in the comment editor to trigger user autocomplete. Select a user to insert a mention. When the comment is saved, the `MentionParser` extracts mentions and:

1. Syncs mention records in the `comment_mentions` table
2. Dispatches a `UserMentioned` event for each newly mentioned user
3. The `SendUserMentionedNotification` listener sends notifications
4. If auto-subscribe is enabled, mentioned users are subscribed to the thread

## Default Resolver

The `DefaultMentionResolver` searches the commenter model using the configured `name_column` (defaults to `name`):

```php
// Searches: User::where('name', 'like', "{$query}%")
// Limited to: config('comments.mentions.max_results') results
```

### Custom Name Column

If your users table uses a different column for names, set `name_column` in the config:

```php
// config/comments.php
'mentions' => [
    'name_column' => 'username', // or 'full_name', etc.
],
```

### Multi-Column Search

If the display name is composed of multiple columns (e.g. `firstname` + `lastname`), configure `search_columns`:

```php
// config/comments.php
'mentions' => [
    'search_columns' => ['firstname', 'lastname'],
],
```

Then register a name resolver in your `AppServiceProvider::boot()`:

```php
use Relaticle\Comments\CommentsConfig;

CommentsConfig::resolveUserNameUsing(
    fn ($user) => $user->firstname . ' ' . $user->lastname
);
```

## Custom Mention Resolver

Implement the `MentionResolver` interface to customize user search behavior:

```php
namespace App\Comments;

use Illuminate\Support\Collection;
use Relaticle\Comments\Contracts\MentionResolver;

class TeamMentionResolver implements MentionResolver
{
    public function search(string $query): Collection
    {
        return User::query()
            ->where('team_id', auth()->user()->team_id)
            ->where('name', 'like', "{$query}%")
            ->limit(config('comments.mentions.max_results'))
            ->get();
    }

    public function resolveByNames(array $names): Collection
    {
        return User::query()
            ->where('team_id', auth()->user()->team_id)
            ->whereIn('name', $names)
            ->get();
    }
}
```

Register it in your config:

```php
// config/comments.php
'mentions' => [
    'resolver' => App\Comments\TeamMentionResolver::class,
    'max_results' => 5,
],
```

## Configuration

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
        mentions.resolver
      </code>
    </td>
    
    <td>
      <code>
        DefaultMentionResolver::class
      </code>
    </td>
    
    <td>
      User search implementation
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        mentions.max_results
      </code>
    </td>
    
    <td>
      <code>
        5
      </code>
    </td>
    
    <td>
      Maximum autocomplete results
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        mentions.name_column
      </code>
    </td>
    
    <td>
      <code>
        'name'
      </code>
    </td>
    
    <td>
      DB column used for display and resolution
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        mentions.search_columns
      </code>
    </td>
    
    <td>
      <code>
        [name_column]
      </code>
    </td>
    
    <td>
      DB columns searched during autocomplete
    </td>
  </tr>
</tbody>
</table>
