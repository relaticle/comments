<?php

use Illuminate\Support\Facades\Notification;
use Relaticle\Comments\Events\CommentCreated;
use Relaticle\Comments\Events\UserMentioned;
use Relaticle\Comments\Listeners\SendCommentRepliedNotification;
use Relaticle\Comments\Listeners\SendUserMentionedNotification;
use Relaticle\Comments\Models\Comment;
use Relaticle\Comments\Models\Subscription;
use Relaticle\Comments\Notifications\CommentRepliedNotification;
use Relaticle\Comments\Notifications\UserMentionedNotification;
use Relaticle\Comments\Tests\Models\Post;
use Relaticle\Comments\Tests\Models\User;

it('returns correct via channels from config for CommentRepliedNotification', function () {
    config()->set('comments.notifications.channels', ['database', 'mail']);

    $user = User::factory()->create();
    $post = Post::factory()->create();

    $comment = Comment::factory()->create([
        'commentable_id' => $post->id,
        'commentable_type' => $post->getMorphClass(),
        'commenter_id' => $user->getKey(),
        'commenter_type' => $user->getMorphClass(),
        'body' => '<p>Hello</p>',
    ]);

    $notification = new CommentRepliedNotification($comment);

    expect($notification->via($user))->toBe(['database', 'mail']);
});

it('returns a filament-format toDatabase payload for CommentRepliedNotification', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $comment = Comment::factory()->create([
        'commentable_id' => $post->id,
        'commentable_type' => $post->getMorphClass(),
        'commenter_id' => $user->getKey(),
        'commenter_type' => $user->getMorphClass(),
        'body' => '<p>This is a reply to @bob</p>',
    ]);

    $notification = new CommentRepliedNotification($comment);
    $data = $notification->toDatabase($user);

    expect($data)->toHaveKeys(['format', 'title', 'body', 'comment_id', 'commentable_type', 'commentable_id', 'commenter_name'])
        ->and($data['format'])->toBe('filament')
        ->and($data['comment_id'])->toBe($comment->id)
        ->and($data['commentable_type'])->toBe($post->getMorphClass())
        ->and($data['commentable_id'])->toBe($post->id)
        ->and($data['commenter_name'])->toBe($user->getCommentDisplayName())
        ->and($data['body'])->toContain('@bob')
        ->and($data['body'])->not->toContain('&#64;');
});

it('decodes html entities in the CommentRepliedNotification mail body', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $comment = Comment::factory()->create([
        'commentable_id' => $post->id,
        'commentable_type' => $post->getMorphClass(),
        'commenter_id' => $user->getKey(),
        'commenter_type' => $user->getMorphClass(),
        'body' => '<p>Reply mentioning @bob here</p>',
    ]);

    $mail = (new CommentRepliedNotification($comment))->toMail($user);
    $lines = implode("\n", array_merge($mail->introLines, $mail->outroLines));

    expect($lines)->toContain('@bob')->not->toContain('&#64;');
});

it('returns correct via channels from config for UserMentionedNotification', function () {
    config()->set('comments.notifications.channels', ['database']);

    $user = User::factory()->create();
    $mentionedBy = User::factory()->create();
    $post = Post::factory()->create();

    $comment = Comment::factory()->create([
        'commentable_id' => $post->id,
        'commentable_type' => $post->getMorphClass(),
        'commenter_id' => $mentionedBy->getKey(),
        'commenter_type' => $mentionedBy->getMorphClass(),
        'body' => '<p>Hey @someone</p>',
    ]);

    $notification = new UserMentionedNotification($comment, $mentionedBy);

    expect($notification->via($user))->toBe(['database']);
});

it('returns a filament-format toDatabase payload for UserMentionedNotification', function () {
    $mentioner = User::factory()->create();
    $mentioned = User::factory()->create();
    $post = Post::factory()->create();

    $comment = Comment::factory()->create([
        'commentable_id' => $post->id,
        'commentable_type' => $post->getMorphClass(),
        'commenter_id' => $mentioner->getKey(),
        'commenter_type' => $mentioner->getMorphClass(),
        'body' => '<p>Hey @mentioned welcome aboard</p>',
    ]);

    $notification = new UserMentionedNotification($comment, $mentioner);
    $data = $notification->toDatabase($mentioned);

    expect($data)->toHaveKeys(['format', 'title', 'body', 'comment_id', 'commentable_type', 'commentable_id', 'mentioner_name'])
        ->and($data['format'])->toBe('filament')
        ->and($data['comment_id'])->toBe($comment->id)
        ->and($data['mentioner_name'])->toBe($mentioner->getCommentDisplayName())
        ->and($data['body'])->toContain('@mentioned')
        ->and($data['body'])->not->toContain('&#64;');
});

it('sends notification to subscribers when reply comment is created', function () {
    Notification::fake();

    $author = User::factory()->create();
    $subscriber = User::factory()->create();
    $post = Post::factory()->create();

    Subscription::subscribe($post, $subscriber);

    $parentComment = Comment::factory()->create([
        'commentable_id' => $post->id,
        'commentable_type' => $post->getMorphClass(),
        'commenter_id' => $subscriber->getKey(),
        'commenter_type' => $subscriber->getMorphClass(),
        'body' => '<p>Original comment</p>',
    ]);

    $reply = Comment::factory()->create([
        'commentable_id' => $post->id,
        'commentable_type' => $post->getMorphClass(),
        'commenter_id' => $author->getKey(),
        'commenter_type' => $author->getMorphClass(),
        'parent_id' => $parentComment->id,
        'body' => '<p>Reply to original</p>',
    ]);

    $listener = new SendCommentRepliedNotification;
    $listener->handle(new CommentCreated($reply));

    Notification::assertSentTo($subscriber, CommentRepliedNotification::class);
});

it('does NOT send notification for top-level comments', function () {
    Notification::fake();

    $author = User::factory()->create();
    $subscriber = User::factory()->create();
    $post = Post::factory()->create();

    Subscription::subscribe($post, $subscriber);

    $comment = Comment::factory()->create([
        'commentable_id' => $post->id,
        'commentable_type' => $post->getMorphClass(),
        'commenter_id' => $author->getKey(),
        'commenter_type' => $author->getMorphClass(),
        'body' => '<p>Top-level comment</p>',
    ]);

    $listener = new SendCommentRepliedNotification;
    $listener->handle(new CommentCreated($comment));

    Notification::assertNothingSent();
});

it('does NOT notify the reply author themselves', function () {
    Notification::fake();

    $user = User::factory()->create();
    $post = Post::factory()->create();

    Subscription::subscribe($post, $user);

    $parentComment = Comment::factory()->create([
        'commentable_id' => $post->id,
        'commentable_type' => $post->getMorphClass(),
        'commenter_id' => $user->getKey(),
        'commenter_type' => $user->getMorphClass(),
        'body' => '<p>My comment</p>',
    ]);

    $reply = Comment::factory()->create([
        'commentable_id' => $post->id,
        'commentable_type' => $post->getMorphClass(),
        'commenter_id' => $user->getKey(),
        'commenter_type' => $user->getMorphClass(),
        'parent_id' => $parentComment->id,
        'body' => '<p>My own reply</p>',
    ]);

    $listener = new SendCommentRepliedNotification;
    $listener->handle(new CommentCreated($reply));

    Notification::assertNotSentTo($user, CommentRepliedNotification::class);
});

it('auto-subscribes comment author to the thread', function () {
    Notification::fake();

    $author = User::factory()->create();
    $post = Post::factory()->create();

    expect(Subscription::isSubscribed($post, $author))->toBeFalse();

    $comment = Comment::factory()->create([
        'commentable_id' => $post->id,
        'commentable_type' => $post->getMorphClass(),
        'commenter_id' => $author->getKey(),
        'commenter_type' => $author->getMorphClass(),
        'body' => '<p>Comment</p>',
    ]);

    $listener = new SendCommentRepliedNotification;
    $listener->handle(new CommentCreated($comment));

    expect(Subscription::isSubscribed($post, $author))->toBeTrue();
});

it('only notifies subscribed users for reply notifications', function () {
    Notification::fake();

    $author = User::factory()->create();
    $subscriber = User::factory()->create();
    $nonSubscriber = User::factory()->create();
    $post = Post::factory()->create();

    Subscription::subscribe($post, $subscriber);

    $parentComment = Comment::factory()->create([
        'commentable_id' => $post->id,
        'commentable_type' => $post->getMorphClass(),
        'commenter_id' => $subscriber->getKey(),
        'commenter_type' => $subscriber->getMorphClass(),
        'body' => '<p>Original</p>',
    ]);

    $reply = Comment::factory()->create([
        'commentable_id' => $post->id,
        'commentable_type' => $post->getMorphClass(),
        'commenter_id' => $author->getKey(),
        'commenter_type' => $author->getMorphClass(),
        'parent_id' => $parentComment->id,
        'body' => '<p>Reply</p>',
    ]);

    $listener = new SendCommentRepliedNotification;
    $listener->handle(new CommentCreated($reply));

    Notification::assertSentTo($subscriber, CommentRepliedNotification::class);
    Notification::assertNotSentTo($nonSubscriber, CommentRepliedNotification::class);
});

it('sends mention notification to mentioned user', function () {
    Notification::fake();

    $author = User::factory()->create();
    $mentioned = User::factory()->create();
    $post = Post::factory()->create();

    $comment = Comment::factory()->create([
        'commentable_id' => $post->id,
        'commentable_type' => $post->getMorphClass(),
        'commenter_id' => $author->getKey(),
        'commenter_type' => $author->getMorphClass(),
        'body' => '<p>Hey @mentioned</p>',
    ]);

    $event = new UserMentioned($comment, $mentioned);
    $listener = new SendUserMentionedNotification;
    $listener->handle($event);

    Notification::assertSentTo($mentioned, UserMentionedNotification::class);
});

it('does NOT send mention notification to the comment author', function () {
    Notification::fake();

    $author = User::factory()->create();
    $post = Post::factory()->create();

    $comment = Comment::factory()->create([
        'commentable_id' => $post->id,
        'commentable_type' => $post->getMorphClass(),
        'commenter_id' => $author->getKey(),
        'commenter_type' => $author->getMorphClass(),
        'body' => '<p>Hey @myself</p>',
    ]);

    $event = new UserMentioned($comment, $author);
    $listener = new SendUserMentionedNotification;
    $listener->handle($event);

    Notification::assertNotSentTo($author, UserMentionedNotification::class);
});

it('auto-subscribes mentioned user to the thread', function () {
    Notification::fake();

    $author = User::factory()->create();
    $mentioned = User::factory()->create();
    $post = Post::factory()->create();

    expect(Subscription::isSubscribed($post, $mentioned))->toBeFalse();

    $comment = Comment::factory()->create([
        'commentable_id' => $post->id,
        'commentable_type' => $post->getMorphClass(),
        'commenter_id' => $author->getKey(),
        'commenter_type' => $author->getMorphClass(),
        'body' => '<p>Hey @mentioned</p>',
    ]);

    $event = new UserMentioned($comment, $mentioned);
    $listener = new SendUserMentionedNotification;
    $listener->handle($event);

    expect(Subscription::isSubscribed($post, $mentioned))->toBeTrue();
});

it('does not send notifications when notifications are disabled', function () {
    Notification::fake();
    config()->set('comments.notifications.enabled', false);

    $author = User::factory()->create();
    $subscriber = User::factory()->create();
    $mentioned = User::factory()->create();
    $post = Post::factory()->create();

    Subscription::subscribe($post, $subscriber);

    $parentComment = Comment::factory()->create([
        'commentable_id' => $post->id,
        'commentable_type' => $post->getMorphClass(),
        'commenter_id' => $subscriber->getKey(),
        'commenter_type' => $subscriber->getMorphClass(),
        'body' => '<p>Original</p>',
    ]);

    $reply = Comment::factory()->create([
        'commentable_id' => $post->id,
        'commentable_type' => $post->getMorphClass(),
        'commenter_id' => $author->getKey(),
        'commenter_type' => $author->getMorphClass(),
        'parent_id' => $parentComment->id,
        'body' => '<p>Reply</p>',
    ]);

    $replyListener = new SendCommentRepliedNotification;
    $replyListener->handle(new CommentCreated($reply));

    $mentionEvent = new UserMentioned($reply, $mentioned);
    $mentionListener = new SendUserMentionedNotification;
    $mentionListener->handle($mentionEvent);

    Notification::assertNothingSent();
});
