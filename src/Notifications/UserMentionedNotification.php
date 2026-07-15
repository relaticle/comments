<?php

namespace Relaticle\Comments\Notifications;

use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use Relaticle\Comments\CommentsConfig;
use Relaticle\Comments\Models\Comment;

class UserMentionedNotification extends Notification
{
    public function __construct(
        public readonly Comment $comment,
        public readonly Model $mentionedBy,
    ) {}

    /** @return array<int, string> */
    public function via(mixed $notifiable): array
    {
        return CommentsConfig::getNotificationChannels();
    }

    /** @return array<string, mixed> */
    public function toDatabase(mixed $notifiable): array
    {
        $mentionerName = $this->mentionedBy->getCommentDisplayName();

        return array_merge(
            FilamentNotification::make()
                ->title(__('comments::comments.notifications.mention_body', ['name' => $mentionerName]))
                ->body($this->bodyExcerpt(100))
                ->icon('heroicon-o-at-symbol')
                ->getDatabaseMessage(),
            [
                'comment_id' => $this->comment->id,
                'commentable_type' => $this->comment->commentable_type,
                'commentable_id' => $this->comment->commentable_id,
                'mentioner_name' => $mentionerName,
            ],
        );
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $mentionerName = $this->mentionedBy->getCommentDisplayName();

        return (new MailMessage)
            ->subject(__('comments::comments.notifications.mention_subject'))
            ->line(__('comments::comments.notifications.mention_body', ['name' => $mentionerName]))
            ->line($this->bodyExcerpt(200));
    }

    protected function bodyExcerpt(int $limit): string
    {
        return Str::limit(
            html_entity_decode(strip_tags($this->comment->body), ENT_QUOTES, 'UTF-8'),
            $limit,
        );
    }
}
