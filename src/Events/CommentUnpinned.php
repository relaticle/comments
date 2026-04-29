<?php

namespace Relaticle\Comments\Events;

use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Relaticle\Comments\CommentsConfig;
use Relaticle\Comments\Models\Comment;

class CommentUnpinned implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithBroadcasting;
    use SerializesModels;

    public readonly Model $commentable;

    public function __construct(public readonly Comment $comment)
    {
        $this->commentable = $comment->commentable;
    }

    /** @return array<int, PrivateChannel> */
    public function broadcastOn(): array
    {
        $prefix = CommentsConfig::getBroadcastChannelPrefix();

        return [
            new PrivateChannel("{$prefix}.{$this->comment->commentable_type}.{$this->comment->commentable_id}"),
        ];
    }

    public function broadcastWhen(): bool
    {
        return CommentsConfig::isBroadcastingEnabled();
    }

    /** @return array{comment_id: int|string, commentable_type: string, commentable_id: int|string} */
    public function broadcastWith(): array
    {
        return [
            'comment_id' => $this->comment->id,
            'commentable_type' => $this->comment->commentable_type,
            'commentable_id' => $this->comment->commentable_id,
        ];
    }
}
