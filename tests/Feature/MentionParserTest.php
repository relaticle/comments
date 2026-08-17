<?php

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Relaticle\Comments\Contracts\MentionResolver;
use Relaticle\Comments\Events\UserMentioned;
use Relaticle\Comments\Mentions\DefaultMentionResolver;
use Relaticle\Comments\Mentions\MentionParser;
use Relaticle\Comments\Models\Comment;
use Relaticle\Comments\Tests\Models\Post;
use Relaticle\Comments\Tests\Models\User;

it('parses rich-editor mention spans using data-id', function () {
    $john = User::factory()->create(['name' => 'john']);

    $parser = app(MentionParser::class);
    $body = '<p>Hello <span data-type="mention" data-id="'.$john->id.'" data-label="john" data-char="@">@john</span></p>';
    $result = $parser->parse($body);

    expect($result)->toHaveCount(1);
    expect($result->first())->toBe($john->id);
});

it('parses rich-editor mention spans whose data-id is not an integer (ulid/uuid commenter keys)', function () {
    $parser = app(MentionParser::class);
    $body = '<p><span data-type="mention" data-id="01m07ry393v7mkm33trc26h8dr" data-label="Tessa" data-char="@">@Tessa</span></p>';

    $result = $parser->parse($body);

    expect($result->all())->toBe(['01m07ry393v7mkm33trc26h8dr']);
});

it('parses @username from plain text body', function () {
    User::factory()->create(['name' => 'john']);
    User::factory()->create(['name' => 'jane']);

    $parser = app(MentionParser::class);
    $result = $parser->parse('Hello @john and @jane');

    expect($result)->toHaveCount(2);
});

it('parses @username from HTML body', function () {
    $john = User::factory()->create(['name' => 'john']);

    $parser = app(MentionParser::class);
    $result = $parser->parse('<p>Hello @john</p>');

    expect($result)->toHaveCount(1);
    expect($result->first())->toBe($john->id);
});

it('returns empty collection when no mentions', function () {
    $parser = app(MentionParser::class);
    $result = $parser->parse('Hello world');

    expect($result)->toBeEmpty();
});

it('returns empty collection for non-existent users', function () {
    $parser = app(MentionParser::class);
    $result = $parser->parse('Hello @ghostuser');

    expect($result)->toBeEmpty();
});

it('handles duplicate @mentions', function () {
    User::factory()->create(['name' => 'john']);

    $parser = app(MentionParser::class);
    $result = $parser->parse('@john said hi @john');

    expect($result)->toHaveCount(1);
});

it('stores mentions in comment_mentions table on create', function () {
    $user = User::factory()->create();
    $john = User::factory()->create(['name' => 'john']);
    $jane = User::factory()->create(['name' => 'jane']);
    $post = Post::factory()->create();

    $comment = Comment::factory()->create([
        'commentable_id' => $post->id,
        'commentable_type' => $post->getMorphClass(),
        'commenter_id' => $user->getKey(),
        'commenter_type' => $user->getMorphClass(),
        'body' => '<p>Hello @john and @jane</p>',
    ]);

    app(MentionParser::class)->syncMentions($comment);

    expect($comment->mentions()->count())->toBe(2);
    expect($comment->mentions->pluck('id')->sort()->values()->all())
        ->toBe(collect([$john->id, $jane->id])->sort()->values()->all());
});

it('dispatches UserMentioned event for each mentioned user', function () {
    Event::fake([UserMentioned::class]);

    $user = User::factory()->create();
    $john = User::factory()->create(['name' => 'john']);
    $jane = User::factory()->create(['name' => 'jane']);
    $post = Post::factory()->create();

    $comment = Comment::factory()->create([
        'commentable_id' => $post->id,
        'commentable_type' => $post->getMorphClass(),
        'commenter_id' => $user->getKey(),
        'commenter_type' => $user->getMorphClass(),
        'body' => '<p>Hello @john and @jane</p>',
    ]);

    app(MentionParser::class)->syncMentions($comment);

    Event::assertDispatched(UserMentioned::class, 2);

    Event::assertDispatched(UserMentioned::class, function (UserMentioned $event) use ($comment, $john) {
        return $event->comment->id === $comment->id
            && $event->mentionedUser->id === $john->id;
    });

    Event::assertDispatched(UserMentioned::class, function (UserMentioned $event) use ($comment, $jane) {
        return $event->comment->id === $comment->id
            && $event->mentionedUser->id === $jane->id;
    });
});

it('only dispatches UserMentioned for newly added mentions on update', function () {
    Event::fake([UserMentioned::class]);

    $user = User::factory()->create();
    $john = User::factory()->create(['name' => 'john']);
    $jane = User::factory()->create(['name' => 'jane']);
    $post = Post::factory()->create();

    $comment = Comment::factory()->create([
        'commentable_id' => $post->id,
        'commentable_type' => $post->getMorphClass(),
        'commenter_id' => $user->getKey(),
        'commenter_type' => $user->getMorphClass(),
        'body' => '<p>Hello @john</p>',
    ]);

    app(MentionParser::class)->syncMentions($comment);

    Event::assertDispatched(UserMentioned::class, 1);

    Event::fake([UserMentioned::class]);

    $comment->update(['body' => '<p>Hello @john and @jane</p>']);
    app(MentionParser::class)->syncMentions($comment->fresh());

    Event::assertDispatched(UserMentioned::class, 1);

    Event::assertDispatched(UserMentioned::class, function (UserMentioned $event) use ($jane) {
        return $event->mentionedUser->id === $jane->id;
    });
});

it('removes mentions from pivot when user removed from body', function () {
    $user = User::factory()->create();
    $john = User::factory()->create(['name' => 'john']);
    $jane = User::factory()->create(['name' => 'jane']);
    $post = Post::factory()->create();

    $comment = Comment::factory()->create([
        'commentable_id' => $post->id,
        'commentable_type' => $post->getMorphClass(),
        'commenter_id' => $user->getKey(),
        'commenter_type' => $user->getMorphClass(),
        'body' => '<p>Hello @john and @jane</p>',
    ]);

    app(MentionParser::class)->syncMentions($comment);

    expect($comment->mentions()->count())->toBe(2);

    $comment->update(['body' => '<p>Hello @john</p>']);
    app(MentionParser::class)->syncMentions($comment->fresh());

    $comment->refresh();

    expect($comment->mentions()->count())->toBe(1);
    expect($comment->mentions->first()->id)->toBe($john->id);
});

it('uses configured MentionResolver', function () {
    $customResolver = new class implements MentionResolver
    {
        public function search(string $query): Collection
        {
            return collect();
        }

        public function resolveByNames(array $names): Collection
        {
            return collect();
        }
    };

    $this->app->instance(MentionResolver::class, $customResolver);

    $parser = app(MentionParser::class);
    $result = $parser->parse('@someuser');

    expect($result)->toBeEmpty();
});

it('searches users by name prefix', function () {
    User::factory()->create(['name' => 'John Doe']);
    User::factory()->create(['name' => 'Jane Doe']);
    User::factory()->create(['name' => 'Bob Smith']);

    $resolver = app(DefaultMentionResolver::class);
    $results = $resolver->search('Jo');

    expect($results)->toHaveCount(1);
    expect($results->first()->name)->toBe('John Doe');
});

it('limits search results to configured max', function () {
    for ($i = 1; $i <= 10; $i++) {
        User::factory()->create(['name' => "Test User {$i}"]);
    }

    config(['comments.mentions.max_results' => 3]);

    $resolver = app(DefaultMentionResolver::class);
    $results = $resolver->search('Test');

    expect($results)->toHaveCount(3);
});

it('resolves users by exact name', function () {
    $john = User::factory()->create(['name' => 'John Doe']);
    User::factory()->create(['name' => 'Jane Doe']);

    $resolver = app(DefaultMentionResolver::class);
    $results = $resolver->resolveByNames(['John Doe']);

    expect($results)->toHaveCount(1);
    expect($results->first()->id)->toBe($john->id);
});
