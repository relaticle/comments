<?php

namespace Relaticle\Comments\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use Relaticle\Comments\CommentsConfig;
use Relaticle\Comments\Models\Comment;

class CommentPolicy
{
    public function viewAny(Authenticatable $user): bool
    {
        return true;
    }

    public function create(Authenticatable $user): bool
    {
        return true;
    }

    public function update(Authenticatable $user, Comment $comment): bool
    {
        return $this->belongsToCurrentTenant($comment)
            && $user->getKey() === $comment->commenter_id
            && $user->getMorphClass() === $comment->commenter_type;
    }

    public function delete(Authenticatable $user, Comment $comment): bool
    {
        return $this->belongsToCurrentTenant($comment)
            && $user->getKey() === $comment->commenter_id
            && $user->getMorphClass() === $comment->commenter_type;
    }

    /**
     * Verify the comment belongs to the currently active tenant.
     *
     * When multi-tenancy is disabled, always returns true.
     * When enabled and the resolver returns null: allows access in CLI/queue context,
     * denies in web context (fail-closed — null resolver = misconfiguration).
     * When enabled and a tenant ID is resolved: strict string comparison against the column.
     */
    private function belongsToCurrentTenant(Comment $comment): bool
    {
        if (! CommentsConfig::isMultiTenancyEnabled()) {
            return true;
        }

        $tenantId = CommentsConfig::resolveTenantId();

        if ($tenantId === null) {
            // CLI / queue workers have no active tenant — allow unrestricted access.
            // In a web request a null resolver means misconfiguration — deny to fail closed.
            return app()->runningInConsole();
        }

        $column = CommentsConfig::getTenantColumn();

        return (string) $comment->{$column} === (string) $tenantId;
    }

    public function reply(Authenticatable $user, Comment $comment): bool
    {
        return $comment->canReply();
    }

    public function pin(Authenticatable $user, Comment $comment): bool
    {
        return false;
    }
}
