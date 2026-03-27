# Attachments

> File uploads for comments.

## Overview

Comments support file attachments for both images and documents. Images are displayed inline within the comment body, while documents appear as downloadable links.

## Configuration

```php
// config/comments.php
'attachments' => [
    'enabled' => true,
    'disk' => 'public',
    'max_size' => 10240, // KB (10 MB)
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
        enabled
      </code>
    </td>
    
    <td>
      <code>
        true
      </code>
    </td>
    
    <td>
      Show/hide the attachment upload UI
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        disk
      </code>
    </td>
    
    <td>
      <code>
        'public'
      </code>
    </td>
    
    <td>
      Laravel filesystem disk for storage
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        max_size
      </code>
    </td>
    
    <td>
      <code>
        10240
      </code>
    </td>
    
    <td>
      Maximum file size in kilobytes
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        allowed_types
      </code>
    </td>
    
    <td>
      images, pdf, text, word
    </td>
    
    <td>
      Array of allowed MIME types
    </td>
  </tr>
</tbody>
</table>

## Disabling Attachments

```php
'attachments' => [
    'enabled' => false,
],
```

This removes the file upload UI from the comment form entirely.

## Storage

Attachments are stored via Livewire's file upload mechanism. Each attachment record tracks:

- `file_path` -- Path on the configured disk
- `original_name` -- Original filename for display
- `mime_type` -- MIME type for rendering decisions
- `size` -- File size in bytes
- `disk` -- Storage disk name

When a comment is deleted, its attachments are cascade deleted from the database. The physical files are removed from the disk.

## Helper Methods

The `Attachment` model (`Relaticle\Comments\Models\Attachment`) provides:

```php
$attachment->isImage();       // Check if attachment is an image
$attachment->url();           // Get the storage URL
$attachment->formattedSize(); // Human-readable size (e.g., "2.5 MB")
```
