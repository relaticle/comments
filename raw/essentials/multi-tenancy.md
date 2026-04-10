# Multi-tenancy

> Isolate comments per tenant with automatic query scoping and zero boilerplate.

Enable this feature when your app serves multiple tenants (teams, organisations, workspaces) from a single database and each tenant must only see their own comments.

## How it works

Three things happen automatically once enabled:

- A `tenant_id` column is added to all five tables via migrations
- Every `Comment` query gets `WHERE tenant_id = ?` applied by a global scope — including queries through `$model->comments()` and `topLevelComments()`
- New comments are stamped with the current tenant ID on creation

You register one closure that resolves the current tenant. Everything else is handled by the package.

## Setup

### 1. Enable in config

Set `enabled` to `true` **before** running migrations — the migration stubs read this value at runtime.

```php
// config/comments.php
'multi_tenancy' => [
    'enabled' => true,
    'tenant_column' => 'tenant_id', // change to team_id, org_id, etc. if needed
    'tenant_resolver' => null,      // register this in a service provider, not here
],
```

### 2. Run migrations

```bash
php artisan migrate
```

The package stubs will add the `tenant_id` column to all five tables.

**Already ran migrations?** Add the column manually:

```php
public function up(): void
{
    $column = config('comments.multi_tenancy.tenant_column', 'tenant_id');

    Schema::table('comments', function (Blueprint $table) use ($column) {
        $table->unsignedBigInteger($column)->nullable()->index()->after('id');
    });

    Schema::table('comment_attachments', function (Blueprint $table) use ($column) {
        $table->unsignedBigInteger($column)->nullable()->index()->after('id');
    });

    Schema::table('comment_mentions', function (Blueprint $table) use ($column) {
        $table->unsignedBigInteger($column)->nullable()->index()->after('id');
    });

    Schema::table('comment_reactions', function (Blueprint $table) use ($column) {
        $table->unsignedBigInteger($column)->nullable()->index()->after('id');
    });

    Schema::table('comment_subscriptions', function (Blueprint $table) use ($column) {
        $table->unsignedBigInteger($column)->nullable()->index()->after('id');
    });
}
```

### 3. Register a resolver

The resolver tells the package which tenant is currently active. Return `null` during CLI commands or queue workers — the scope is skipped automatically when `null` is returned.

```php
// AppServiceProvider::boot()
use Relaticle\Comments\CommentsConfig;

CommentsConfig::resolveTenantUsing(fn (): int|string|null => auth()->user()?->team_id);
```

## Framework examples

### Filament

```php
use Filament\Facades\Filament;
use Relaticle\Comments\CommentsConfig;

CommentsConfig::resolveTenantUsing(fn () => Filament::getTenant()?->getKey());
```

`Filament::getTenant()` returns `null` outside a panel request, so artisan and queue contexts are safe without any extra handling.

### Spatie Laravel-Multitenancy

```php
use Spatie\Multitenancy\Models\Tenant;
use Relaticle\Comments\CommentsConfig;

CommentsConfig::resolveTenantUsing(fn () => Tenant::current()?->getKey());
```

## Bypassing the scope

To query across all tenants in an admin panel or console command:

```php
use Relaticle\Comments\Scopes\TenantScope;

Comment::withoutGlobalScope(TenantScope::class)->get();
```

## Authorization

The global scope filters queries. The `CommentPolicy` independently verifies tenant ownership on `update` and `delete`. Both layers enforce the boundary — the policy does not assume the comment was loaded through a scoped query.
