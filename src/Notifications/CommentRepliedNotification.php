<?php

namespace Relaticle\Comments\Notifications;

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
        return [
            'comment_id' => $this->comment->id,
            'commentable_type' => $this->comment->commentable_type,
            'commentable_id' => $this->comment->commentable_id,
            'commenter_name' => $this->comment->commenter->getCommentDisplayName(),
            'body' => Str::limit(strip_tags($this->comment->body), 100),
        ];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $commenterName = $this->comment->commenter->getCommentDisplayName();

        return (new MailMessage)
            ->subject(__('comments::comments.notifications.reply_subject'))
            ->line(__('comments::comments.notifications.reply_body', ['name' => $commenterName]))
            ->line(Str::limit(strip_tags($this->comment->body), 200));
    }
}
