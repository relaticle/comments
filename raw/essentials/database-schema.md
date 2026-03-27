# Database Schema

> Tables, relationships, and indexes used by the Comments package.

## Tables

Five tables are created by the package migrations.

### comments

The main comments table with polymorphic relationships and threading support.

<table>
<thead>
  <tr>
    <th>
      Column
    </th>
    
    <th>
      Type
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
        id
      </code>
    </td>
    
    <td>
      bigint
    </td>
    
    <td>
      Primary key
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        commentable_type
      </code>
    </td>
    
    <td>
      string
    </td>
    
    <td>
      Polymorphic model type
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        commentable_id
      </code>
    </td>
    
    <td>
      bigint
    </td>
    
    <td>
      Polymorphic model ID
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        commenter_type
      </code>
    </td>
    
    <td>
      string
    </td>
    
    <td>
      Commenter model type
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        commenter_id
      </code>
    </td>
    
    <td>
      bigint
    </td>
    
    <td>
      Commenter model ID
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        parent_id
      </code>
    </td>
    
    <td>
      bigint (nullable)
    </td>
    
    <td>
      Parent comment for replies
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        body
      </code>
    </td>
    
    <td>
      text
    </td>
    
    <td>
      HTML comment content
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        edited_at
      </code>
    </td>
    
    <td>
      timestamp (nullable)
    </td>
    
    <td>
      When the comment was last edited
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        deleted_at
      </code>
    </td>
    
    <td>
      timestamp (nullable)
    </td>
    
    <td>
      Soft delete timestamp
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        created_at
      </code>
    </td>
    
    <td>
      timestamp
    </td>
    
    <td>
      
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        updated_at
      </code>
    </td>
    
    <td>
      timestamp
    </td>
    
    <td>
      
    </td>
  </tr>
</tbody>
</table>

**Indexes:** `(commentable_type, commentable_id, parent_id)`

### comment_reactions

Tracks emoji reactions per user per comment.

<table>
<thead>
  <tr>
    <th>
      Column
    </th>
    
    <th>
      Type
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
        id
      </code>
    </td>
    
    <td>
      bigint
    </td>
    
    <td>
      Primary key
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        comment_id
      </code>
    </td>
    
    <td>
      bigint
    </td>
    
    <td>
      Foreign key to comments
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        commenter_type
      </code>
    </td>
    
    <td>
      string
    </td>
    
    <td>
      Reactor model type
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        commenter_id
      </code>
    </td>
    
    <td>
      bigint
    </td>
    
    <td>
      Reactor model ID
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        reaction
      </code>
    </td>
    
    <td>
      string
    </td>
    
    <td>
      Reaction key (e.g., <code>
        thumbs_up
      </code>
      
      )
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        created_at
      </code>
    </td>
    
    <td>
      timestamp
    </td>
    
    <td>
      
    </td>
  </tr>
</tbody>
</table>

**Unique constraint:** `(comment_id, commenter_id, commenter_type, reaction)`

### comment_mentions

Tracks @mentioned users per comment.

<table>
<thead>
  <tr>
    <th>
      Column
    </th>
    
    <th>
      Type
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
        id
      </code>
    </td>
    
    <td>
      bigint
    </td>
    
    <td>
      Primary key
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        comment_id
      </code>
    </td>
    
    <td>
      bigint
    </td>
    
    <td>
      Foreign key to comments
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        commenter_type
      </code>
    </td>
    
    <td>
      string
    </td>
    
    <td>
      Mentioned user model type
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        commenter_id
      </code>
    </td>
    
    <td>
      bigint
    </td>
    
    <td>
      Mentioned user model ID
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        created_at
      </code>
    </td>
    
    <td>
      timestamp
    </td>
    
    <td>
      
    </td>
  </tr>
</tbody>
</table>

**Unique constraint:** `(comment_id, commenter_id, commenter_type)`

### comment_subscriptions

Tracks which users are subscribed to comment threads on specific models.

<table>
<thead>
  <tr>
    <th>
      Column
    </th>
    
    <th>
      Type
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
        id
      </code>
    </td>
    
    <td>
      bigint
    </td>
    
    <td>
      Primary key
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        commentable_type
      </code>
    </td>
    
    <td>
      string
    </td>
    
    <td>
      Subscribed model type
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        commentable_id
      </code>
    </td>
    
    <td>
      bigint
    </td>
    
    <td>
      Subscribed model ID
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        commenter_type
      </code>
    </td>
    
    <td>
      string
    </td>
    
    <td>
      Subscriber model type
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        commenter_id
      </code>
    </td>
    
    <td>
      bigint
    </td>
    
    <td>
      Subscriber model ID
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        created_at
      </code>
    </td>
    
    <td>
      timestamp
    </td>
    
    <td>
      
    </td>
  </tr>
</tbody>
</table>

**Unique constraint:** `(commentable_type, commentable_id, commenter_type, commenter_id)`

### comment_attachments

Stores file attachment metadata for comments.

<table>
<thead>
  <tr>
    <th>
      Column
    </th>
    
    <th>
      Type
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
        id
      </code>
    </td>
    
    <td>
      bigint
    </td>
    
    <td>
      Primary key
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        comment_id
      </code>
    </td>
    
    <td>
      bigint
    </td>
    
    <td>
      Foreign key to comments
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        file_path
      </code>
    </td>
    
    <td>
      string
    </td>
    
    <td>
      Path on the storage disk
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        original_name
      </code>
    </td>
    
    <td>
      string
    </td>
    
    <td>
      Original uploaded filename
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        mime_type
      </code>
    </td>
    
    <td>
      string
    </td>
    
    <td>
      File MIME type
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        size
      </code>
    </td>
    
    <td>
      bigint
    </td>
    
    <td>
      File size in bytes
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        disk
      </code>
    </td>
    
    <td>
      string
    </td>
    
    <td>
      Laravel filesystem disk
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        created_at
      </code>
    </td>
    
    <td>
      timestamp
    </td>
    
    <td>
      
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        updated_at
      </code>
    </td>
    
    <td>
      timestamp
    </td>
    
    <td>
      
    </td>
  </tr>
</tbody>
</table>

## Relationships

```text
Commentable Model (e.g., Project)
  └── comments (morphMany)
        ├── commenter (morphTo → User)
        ├── parent (belongsTo → Comment)
        ├── replies (hasMany → Comment)
        ├── reactions (hasMany → Reaction)
        ├── attachments (hasMany → Attachment)
        └── mentions (morphToMany → User)
```

All relationships are polymorphic, allowing the same comment system to work across any number of models in your application.
