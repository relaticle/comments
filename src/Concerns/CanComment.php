<?php

namespace Relaticle\Comments\Concerns;

use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasName;

trait CanComment
{
    public function getCommentDisplayName(): string
    {
        if ($this instanceof HasName) {
            return $this->getFilamentName();
        }

        return $this->name ?? __('comments::comments.unknown_user');
    }

    public function getCommentAvatarUrl(): ?string
    {
        if ($this instanceof HasAvatar) {
            return $this->getFilamentAvatarUrl();
        }

        return null;
    }
}
