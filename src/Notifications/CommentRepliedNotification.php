<?php

namespace Relaticle\Comments\Notifications;

use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use Relaticle\Comments\CommentsConfig;
use Relaticle\Comments\Models\Comment;

class CommentRepliedNotification extends Notification
{
    public function __construct(public readonly Comment $comment) {}

    /** @return array<int, string> */
    public function via(mixed $notifiable): array
    {
        return CommentsConfig::getNotificationChannels();
    }

    /** @return array<string, mixed> */
    public function toDatabase(mixed $notifiable): array
    {
        $commenterName = $this->comment->commenter->getCommentDisplayName();

        return array_merge(
            FilamentNotification::make()
                ->title(__('comments::comments.notifications.reply_body', ['name' => $commenterName]))
                ->body($this->bodyExcerpt(100))
                ->icon('heroicon-o-chat-bubble-left-right')
                ->getDatabaseMessage(),
            [
                'comment_id' => $this->comment->id,
                'commentable_type' => $this->comment->commentable_type,
                'commentable_id' => $this->comment->commentable_id,
                'commenter_name' => $commenterName,
            ],
        );
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $commenterName = $this->comment->commenter->getCommentDisplayName();

        return (new MailMessage)
            ->subject(__('comments::comments.notifications.reply_subject'))
            ->line(__('comments::comments.notifications.reply_body', ['name' => $commenterName]))
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
