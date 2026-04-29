<?php

use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Relaticle\Comments\CommentsConfig;
use Relaticle\Comments\Events\CommentPinned;
use Relaticle\Comments\Events\CommentUnpinned;
use Relaticle\Comments\Livewire\Comments;
use Relaticle\Comments\Models\Comment;
use Relaticle\Comments\Tests\Models\Post;
use Relaticle\Comments\Tests\Models\User;

beforeEach(function () {
    CommentsConfig::authorizePinUsing(fn ($user, $comment) => true);
});

afterEach(function () {
    // Reset pin authorization callback after each test
    CommentsConfig::authorizePinUsing(fn ($user, $comment) => false);
});

// ── Model helpers ────────────────────────────────────────────────────────────

it('isPinned returns false for a new comment', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();
    $comment = Comment::factory()->create([
        'commentable_id' => $post->id,
        'commentable_type' => $post->getMorphClass(),
        'commenter_id' => $user->getKey(),
        'commenter_type' => $user->getMorphClass(),
    ]);
    expect($comment->isPinned())->toBeFalse();
});

it('pin() sets pinned_at timestamp', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();
    $comment = Comment::factory()->create([
        'commentable_id' => $post->id,
        'commentable_type' => $post->getMorphClass(),
        'commenter_id' => $user->getKey(),
        'commenter_type' => $user->getMorphClass(),
    ]);
    $comment->pin();
    expect($comment->fresh()->isPinned())->toBeTrue();
    expect($comment->fresh()->pinned_at)->not->toBeNull();
});

it('unpin() clears pinned_at', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();
    $comment = Comment::factory()->create([
        'commentable_id' => $post->id,
        'commentable_type' => $post->getMorphClass(),
        'commenter_id' => $user->getKey(),
        'commenter_type' => $user->getMorphClass(),
        'pinned_at' => now(),
    ]);
    $comment->unpin();
    expect($comment->fresh()->isPinned())->toBeFalse();
    expect($comment->fresh()->pinned_at)->toBeNull();
});

it('scopePinned returns only pinned comments', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $attrs = [
        'commentable_id' => $post->id,
        'commentable_type' => $post->getMorphClass(),
        'commenter_id' => $user->getKey(),
        'commenter_type' => $user->getMorphClass(),
    ];

    Comment::factory()->create($attrs);
    $pinned = Comment::factory()->create(array_merge($attrs, ['pinned_at' => now()]));

    $results = $post->topLevelComments()->pinned()->get();

    expect($results)->toHaveCount(1);
    expect($results->first()->id)->toBe($pinned->id);
});

it('scopeUnpinned returns only unpinned comments', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $attrs = [
        'commentable_id' => $post->id,
        'commentable_type' => $post->getMorphClass(),
        'commenter_id' => $user->getKey(),
        'commenter_type' => $user->getMorphClass(),
    ];

    $unpinned = Comment::factory()->create($attrs);
    Comment::factory()->create(array_merge($attrs, ['pinned_at' => now()]));

    $results = $post->topLevelComments()->unpinned()->get();

    expect($results)->toHaveCount(1);
    expect($results->first()->id)->toBe($unpinned->id);
});

// ── Livewire pin/unpin ───────────────────────────────────────────────────────

it('pinComment pins a top-level comment and fires CommentPinned event', function () {
    Event::fake([CommentPinned::class]);

    $user = User::factory()->create();
    $post = Post::factory()->create();

    $comment = Comment::factory()->create([
        'commentable_id' => $post->id,
        'commentable_type' => $post->getMorphClass(),
        'commenter_id' => $user->getKey(),
        'commenter_type' => $user->getMorphClass(),
    ]);

    $this->actingAs($user);

    Livewire::test(Comments::class, ['model' => $post])
        ->call('pinComment', $comment->id);

    expect($comment->fresh()->isPinned())->toBeTrue();

    Event::assertDispatched(CommentPinned::class, fn ($e) => $e->comment->id === $comment->id);
});

it('unpinComment unpins a comment and fires CommentUnpinned event', function () {
    Event::fake([CommentUnpinned::class]);

    $user = User::factory()->create();
    $post = Post::factory()->create();

    $comment = Comment::factory()->create([
        'commentable_id' => $post->id,
        'commentable_type' => $post->getMorphClass(),
        'commenter_id' => $user->getKey(),
        'commenter_type' => $user->getMorphClass(),
        'pinned_at' => now(),
    ]);

    $this->actingAs($user);

    Livewire::test(Comments::class, ['model' => $post])
        ->call('unpinComment', $comment->id);

    expect($comment->fresh()->isPinned())->toBeFalse();

    Event::assertDispatched(CommentUnpinned::class, fn ($e) => $e->comment->id === $comment->id);
});

it('pinComment is blocked when user is not authorized', function () {
    CommentsConfig::authorizePinUsing(fn ($user, $comment) => false);

    $user = User::factory()->create();
    $post = Post::factory()->create();

    $comment = Comment::factory()->create([
        'commentable_id' => $post->id,
        'commentable_type' => $post->getMorphClass(),
        'commenter_id' => $user->getKey(),
        'commenter_type' => $user->getMorphClass(),
    ]);

    $this->actingAs($user);

    Livewire::test(Comments::class, ['model' => $post])
        ->call('pinComment', $comment->id);

    expect($comment->fresh()->isPinned())->toBeFalse();
});

it('pinComment cannot pin a comment from a different thread', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();
    $otherPost = Post::factory()->create();

    $otherComment = Comment::factory()->create([
        'commentable_id' => $otherPost->id,
        'commentable_type' => $otherPost->getMorphClass(),
        'commenter_id' => $user->getKey(),
        'commenter_type' => $user->getMorphClass(),
    ]);

    $this->actingAs($user);

    Livewire::test(Comments::class, ['model' => $post])
        ->call('pinComment', $otherComment->id);

    expect($otherComment->fresh()->isPinned())->toBeFalse();
});

it('pinComment respects max_pinned limit', function () {
    config(['comments.pinning.max_pinned' => 2]);

    $user = User::factory()->create();
    $post = Post::factory()->create();

    $attrs = [
        'commentable_id' => $post->id,
        'commentable_type' => $post->getMorphClass(),
        'commenter_id' => $user->getKey(),
        'commenter_type' => $user->getMorphClass(),
    ];

    Comment::factory()->create(array_merge($attrs, ['pinned_at' => now()]));
    Comment::factory()->create(array_merge($attrs, ['pinned_at' => now()]));
    $third = Comment::factory()->create($attrs);

    $this->actingAs($user);

    Livewire::test(Comments::class, ['model' => $post])
        ->call('pinComment', $third->id);

    expect($third->fresh()->isPinned())->toBeFalse();
});

it('pinnedComments computed excludes unpinned comments', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $attrs = [
        'commentable_id' => $post->id,
        'commentable_type' => $post->getMorphClass(),
        'commenter_id' => $user->getKey(),
        'commenter_type' => $user->getMorphClass(),
    ];

    Comment::factory()->create($attrs);
    $pinned = Comment::factory()->create(array_merge($attrs, ['pinned_at' => now()]));

    $this->actingAs($user);

    $component = Livewire::test(Comments::class, ['model' => $post]);
    $pinnedCollection = $component->instance()->pinnedComments();

    expect($pinnedCollection)->toHaveCount(1);
    expect($pinnedCollection->first()->id)->toBe($pinned->id);
});

it('comments computed excludes pinned comments', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $attrs = [
        'commentable_id' => $post->id,
        'commentable_type' => $post->getMorphClass(),
        'commenter_id' => $user->getKey(),
        'commenter_type' => $user->getMorphClass(),
    ];

    $unpinned = Comment::factory()->create($attrs);
    Comment::factory()->create(array_merge($attrs, ['pinned_at' => now()]));

    $this->actingAs($user);

    $component = Livewire::test(Comments::class, ['model' => $post]);
    $comments = $component->instance()->comments();

    expect($comments)->toHaveCount(1);
    expect($comments->first()->id)->toBe($unpinned->id);
});

it('pinnedComments returns empty when pinning is disabled', function () {
    config(['comments.pinning.enabled' => false]);

    $user = User::factory()->create();
    $post = Post::factory()->create();

    Comment::factory()->create([
        'commentable_id' => $post->id,
        'commentable_type' => $post->getMorphClass(),
        'commenter_id' => $user->getKey(),
        'commenter_type' => $user->getMorphClass(),
        'pinned_at' => now(),
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Comments::class, ['model' => $post]);

    expect($component->instance()->pinnedComments())->toHaveCount(0);
});
