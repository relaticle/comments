<?php

use Relaticle\Comments\Filament\Actions\CommentsTableAction;
use Relaticle\Comments\Models\Comment;
use Relaticle\Comments\Tests\Models\Post;
use Relaticle\Comments\Tests\Models\User;

it('can be instantiated via make', function () {
    $action = CommentsTableAction::make('comments');

    expect($action)->toBeInstanceOf(CommentsTableAction::class);
});

it('configures as a slide-over', function () {
    $action = CommentsTableAction::make('comments');

    expect($action->isModalSlideOver())->toBeTrue();
});

it('disables modal submit and cancel actions', function () {
    $action = CommentsTableAction::make('comments');

    expect($action->getModalSubmitAction())->toBeFalsy()
        ->and($action->getModalCancelAction())->toBeFalsy();
});

it('shows badge with comment count for the record', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    Comment::factory()->count(5)->create([
        'commentable_id' => $post->id,
        'commentable_type' => $post->getMorphClass(),
        'commenter_id' => $user->getKey(),
        'commenter_type' => $user->getMorphClass(),
    ]);

    $action = CommentsTableAction::make('comments');
    $action->record($post);

    expect($action->getBadge())->toBe('5');
});
