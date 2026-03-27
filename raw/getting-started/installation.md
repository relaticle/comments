# Installation

> Get started with Comments in minutes.

## Requirements

- **PHP:** 8.2+
- **Laravel:** 12+
- **Filament:** 4.x / 5.x
- **Livewire:** 3.5+ / 4.x

## Quick Setup

<steps>

### Install Package

```bash [Terminal]
composer require relaticle/comments
```

### Publish and Run Migrations

```bash [Terminal]
php artisan vendor:publish --tag=comments-migrations
php artisan migrate
```

### Include CSS Assets

Prerequisite: You need a custom Filament theme to include the Comments styles.

<alert type="warning">

If you haven't set up a custom theme for Filament, follow the [Filament Docs](https://filamentphp.com/docs/5.x/styling/overview#creating-a-custom-theme) first.

</alert>

Add the plugin's views to your theme CSS file:

```css [resources/css/filament/admin/theme.css]
@source "../../../../vendor/relaticle/comments/resources/views/**/*.blade.php";
```

### Register the Plugin

```php [AdminPanelProvider.php]
use Relaticle\Comments\CommentsPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            CommentsPlugin::make(),
        ]);
}
```

### Set Up Your Models

Add the `HasComments` trait to any model you want to comment on:

```php [app/Models/Project.php]
use Relaticle\Comments\Concerns\HasComments;
use Relaticle\Comments\Contracts\Commentable;

class Project extends Model implements Commentable
{
    use HasComments;
}
```

Add the `IsCommenter` trait to your User model:

```php [app/Models/User.php]
use Relaticle\Comments\Concerns\IsCommenter;
use Relaticle\Comments\Contracts\Commenter;

class User extends Authenticatable implements Commenter
{
    use IsCommenter;
}
```

### Add to Your Resources

Use the slide-over action on view or edit pages:

```php [app/Filament/Resources/ProjectResource/Pages/ViewProject.php]
use Relaticle\Comments\Filament\Actions\CommentsAction;

protected function getHeaderActions(): array
{
    return [
        CommentsAction::make(),
    ];
}
```

</steps>

**Done!** Visit your Filament panel to see comments in action.

## Optional Configuration

<table>
<thead>
  <tr>
    <th>
      Command
    </th>
    
    <th>
      Action
    </th>
  </tr>
</thead>

<tbody>
  <tr>
    <td>
      <code>
        php artisan vendor:publish --tag=comments-config
      </code>
    </td>
    
    <td>
      Publish the configuration file
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        php artisan vendor:publish --tag=comments-views
      </code>
    </td>
    
    <td>
      Publish the Blade views for customization
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        php artisan vendor:publish --tag=comments-translations
      </code>
    </td>
    
    <td>
      Publish the translation files
    </td>
  </tr>
</tbody>
</table>
