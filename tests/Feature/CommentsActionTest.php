<?php

use Relaticle\Comments\Filament\Actions\CommentsAction;
use Relaticle\Comments\Models\Comment;
use Relaticle\Comments\Tests\Models\Post;
use Relaticle\Comments\Tests\Models\User;

it('can be instantiated via make', function () {
    $action = CommentsAction::make('comments');

    expect($action)->toBeInstanceOf(CommentsAction::class);
});

it('has the correct default name', function () {
    $action = CommentsAction::make('comments');

    expect($action->getName())->toBe('comments');
});

it('configures as a slide-over', function () {
    $action = CommentsAction::make('comments');

    expect($action->isModalSlideOver())->toBeTrue();
});

it('has a chat bubble icon', function () {
    $action = CommentsAction::make('comments');

    expect($action->getIcon())->toBe('heroicon-o-chat-bubble-left-right');
});

it('disables modal submit and cancel actions', function () {
    $action = CommentsAction::make('comments');

    expect($action->getModalSubmitAction())->toBeFalsy()
        ->and($action->getModalCancelAction())->toBeFalsy();
});

it('shows badge with comment count when comments exist', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    Comment::factory()->count(3)->create([
        'commentable_id' => $post->id,
        'commentable_type' => $post->getMorphClass(),
        'commenter_id' => $user->getKey(),
        'commenter_type' => $user->getMorphClass(),
    ]);

    $action = CommentsAction::make('comments');
    $action->record($post);

    expect((int) $action->getBadge())->toBe(3);
});

it('returns null badge when no comments exist', function () {
    $post = Post::factory()->create();

    $action = CommentsAction::make('comments');
    $action->record($post);

    expect($action->getBadge())->toBeNull();
});
