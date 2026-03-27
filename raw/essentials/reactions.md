# Reactions

> Emoji reactions on comments.

## Default Reactions

Six emoji reactions are available out of the box:

<table>
<thead>
  <tr>
    <th>
      Key
    </th>
    
    <th>
      Emoji
    </th>
    
    <th>
      Label
    </th>
  </tr>
</thead>

<tbody>
  <tr>
    <td>
      <code>
        thumbs_up
      </code>
    </td>
    
    <td>
      :thumbsup:
    </td>
    
    <td>
      Like
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        heart
      </code>
    </td>
    
    <td>
      ❤️
    </td>
    
    <td>
      Love
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        celebrate
      </code>
    </td>
    
    <td>
      🎉
    </td>
    
    <td>
      Celebrate
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        laugh
      </code>
    </td>
    
    <td>
      😄
    </td>
    
    <td>
      Laugh
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        thinking
      </code>
    </td>
    
    <td>
      🤔
    </td>
    
    <td>
      Thinking
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        sad
      </code>
    </td>
    
    <td>
      😢
    </td>
    
    <td>
      Sad
    </td>
  </tr>
</tbody>
</table>

## How Reactions Work

- Each user can add one reaction of each type per comment
- Clicking the same reaction again removes it (toggle behavior)
- The reaction summary shows which users reacted with each emoji
- A `CommentReacted` event is dispatched with `action: 'added'` or `'removed'`

## Customizing Reactions

Override the emoji set in your config:

```php
// config/comments.php
'reactions' => [
    'emoji_set' => [
        'thumbs_up' => "\u{1F44D}",
        'thumbs_down' => "\u{1F44E}",
        'heart' => "\u{2764}\u{FE0F}",
        'fire' => "\u{1F525}",
        'eyes' => "\u{1F440}",
    ],
],
```

Keys are stored in the database. If you change a key, existing reactions with the old key will no longer display.

## Storage

Reactions are stored in the `comment_reactions` table with a unique constraint on `(comment_id, commenter_id, commenter_type, reaction)`, ensuring one reaction of each type per user per comment.
