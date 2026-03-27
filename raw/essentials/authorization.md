# Authorization

> Control who can create, edit, delete, and reply to comments.

## Default Policy

The built-in `CommentPolicy` provides sensible defaults:

<table>
<thead>
  <tr>
    <th>
      Method
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
        viewAny()
      </code>
    </td>
    
    <td>
      <code>
        true
      </code>
    </td>
    
    <td>
      Everyone can view comments
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        create()
      </code>
    </td>
    
    <td>
      <code>
        true
      </code>
    </td>
    
    <td>
      Everyone can create comments
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        update()
      </code>
    </td>
    
    <td>
      Owner only
    </td>
    
    <td>
      Only the comment author can edit
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        delete()
      </code>
    </td>
    
    <td>
      Owner only
    </td>
    
    <td>
      Only the comment author can delete
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        reply()
      </code>
    </td>
    
    <td>
      Depth check
    </td>
    
    <td>
      Can reply if <code>
        max_depth
      </code>
      
       not exceeded
    </td>
  </tr>
</tbody>
</table>

## Custom Policy

Create your own policy to customize authorization:

```php
namespace App\Policies;

use Relaticle\Comments\Models\Comment;
use Relaticle\Comments\Contracts\Commentator;

class CustomCommentPolicy
{
    public function viewAny(Commentator $user): bool
    {
        return true;
    }

    public function create(Commentator $user): bool
    {
        return true;
    }

    public function update(Commentator $user, Comment $comment): bool
    {
        return $comment->commenter_id === $user->getKey()
            && $comment->commenter_type === $user->getMorphClass();
    }

    public function delete(Commentator $user, Comment $comment): bool
    {
        return $comment->commenter_id === $user->getKey()
            || $user->hasRole('admin');
    }

    public function reply(Commentator $user, Comment $comment): bool
    {
        return $comment->canReply();
    }
}
```

Register it in your config:

```php
// config/comments.php
'policy' => App\Policies\CustomCommentPolicy::class,
```

## How Authorization Works

The Livewire components check the policy before rendering action buttons. Edit and delete buttons only appear for authorized users. Reply buttons are hidden when the thread has reached the configured `max_depth`.

The policy is registered automatically by the service provider using Laravel's Gate system.
