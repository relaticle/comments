<?php

use Illuminate\Support\Collection;
use Livewire\Livewire;
use Relaticle\Comments\CommentsConfig;
use Relaticle\Comments\Contracts\MentionResolver;
use Relaticle\Comments\Livewire\CommentItem;
use Relaticle\Comments\Livewire\Comments;
use Relaticle\Comments\Models\Comment;
use Relaticle\Comments\Tests\Models\Post;
use Relaticle\Comments\Tests\Models\User;

it('stores mentions when creating comment with @mention', function () {
    $user = User::factory()->create();
    $alice = User::factory()->create(['name' => 'Alice']);
    $post = Post::factory()->create();

    $this->actingAs($user);

    Livewire::test(Comments::class, ['model' => $post])
        ->set('commentData.body', '<p>Hey @Alice check this</p>')
        ->call('addComment');

    $comment = Comment::first();

    expect($comment->mentions)->toHaveCount(1);
    expect($comment->mentions->first()->id)->toBe($alice->id);
});

it('stores mentions when editing comment with @mention', function () {
    $user = User::factory()->create();
    $bob = User::factory()->create(['name' => 'Bob']);
    $post = Post::factory()->create();

    $comment = Comment::factory()->create([
        'commentable_id' => $post->id,
        'commentable_type' => $post->getMorphClass(),
        'commenter_id' => $user->getKey(),
        'commenter_type' => $user->getMorphClass(),
        'body' => '<p>Original comment</p>',
    ]);

    $this->actingAs($user);

    Livewire::test(CommentItem::class, ['comment' => $comment])
        ->call('startEdit')
        ->set('editData.body', '<p>Updated @Bob</p>')
        ->call('saveEdit');

    $comment->refresh();

    expect($comment->mentions)->toHaveCount(1);
    expect($comment->mentions->first()->id)->toBe($bob->id);
});

it('orders mention search results by the first search column, not the name column', function () {
    config()->set('comments.mentions.search_columns', ['name']);
    config()->set('comments.mentions.name_column', 'email');

    $first = User::factory()->create(['name' => 'Aaron Example', 'email' => 'zzz@example.com']);
    User::factory()->create(['name' => 'Zoe Example', 'email' => 'aaa@example.com']);

    $results = CommentsConfig::makeMentionProvider()->getSearchResults('Example');

    expect((int) array_key_first($results))->toBe((int) $first->getKey());
});

it('delegates mention autocomplete to the bound mention resolver', function () {
    $alice = User::factory()->create(['name' => 'Alice']);
    User::factory()->create(['name' => 'Alicia']);

    app()->bind(MentionResolver::class, fn () => new class implements MentionResolver
    {
        public function search(string $query): Collection
        {
            return User::query()->where('name', 'Alice')->get();
        }

        public function resolveByNames(array $names): Collection
        {
            return User::query()->whereIn('name', $names)->get();
        }
    });

    $results = CommentsConfig::makeMentionProvider()->getSearchResults('Ali');

    expect($results)->toBe([$alice->getKey() => 'Alice']);
});
