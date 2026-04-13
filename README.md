# Comments

<img src="art/preview.png" alt="Comments System" width="800">

A full-featured commenting system for Filament panels with threaded replies, @mentions, emoji reactions, and real-time updates.

[![Latest Version](https://img.shields.io/packagist/v/relaticle/comments.svg?style=for-the-badge)](https://packagist.org/packages/relaticle/comments)
[![Total Downloads](https://img.shields.io/packagist/dt/relaticle/comments.svg?style=for-the-badge)](https://packagist.org/packages/relaticle/comments)
[![PHP 8.2+](https://img.shields.io/badge/php-8.2%2B-blue.svg?style=for-the-badge)](https://php.net)
[![Laravel 12+](https://img.shields.io/badge/laravel-12%2B-red.svg?style=for-the-badge)](https://laravel.com)
[![Tests](https://img.shields.io/github/actions/workflow/status/relaticle/comments/tests.yml?branch=1.x&style=for-the-badge&label=tests)](https://github.com/relaticle/comments/actions)

## Features

- **Threaded Replies** - Nested comment threads with configurable depth limits
- **@Mentions** - Autocomplete user mentions with customizable resolver
- **Emoji Reactions** - 6 built-in reactions with configurable emoji sets
- **File Attachments** - Image and document uploads with validation
- **Notifications & Subscriptions** - Database and mail notifications with auto-subscribe
- **Multi-tenancy** - Built-in tenant isolation for multi-tenant applications
- **3 Filament Integrations** - Slide-over action, table action, and infolist entry

## Requirements

- **PHP:** 8.2+
- **Laravel:** 12+
- **Livewire:** 3.5+ / 4.x
- **Filament:** 4.x / 5.x

## Getting Started

```bash
composer require relaticle/comments
```

Publish and run migrations:

```bash
php artisan vendor:publish --tag=comments-migrations
php artisan migrate
```

### Setting Up Your Models

```php
use Relaticle\Comments\Concerns\HasComments;
use Relaticle\Comments\Contracts\Commentable;

class Project extends Model implements Commentable
{
    use HasComments;
}
```

### Register the Filament Plugin

```php
use Relaticle\Comments\CommentsPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            CommentsPlugin::make(),
        ]);
}
```

### Add Comments to Your Resources

```php
use Relaticle\Comments\Filament\Actions\CommentsAction;

protected function getHeaderActions(): array
{
    return [
        CommentsAction::make(),
    ];
}
```

## Documentation

For complete installation instructions, configuration options, multi-tenancy setup, and examples, visit our [documentation](https://relaticle.github.io/comments/).

## Contributing

Contributions are welcome! Please see our [contributing guide](https://relaticle.github.io/comments/) in the documentation.

## License

MIT License. See [LICENSE](LICENSE) for details.
