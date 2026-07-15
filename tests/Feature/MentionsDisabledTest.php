<?php

use Filament\Forms\Components\RichEditor;
use Illuminate\Support\Facades\Event;
use Relaticle\Comments\CommentsConfig;
use Relaticle\Comments\Events\UserMentioned;
use Relaticle\Comments\Mentions\MentionParser;
use Relaticle\Comments\Models\Comment;
use Relaticle\Comments\Tests\Models\Post;
use Relaticle\Comments\Tests\Models\User;

it('is enabled by default', function () {
    expect(config('comments.mentions.enabled'))->toBeTrue();
});

it('does not sync mentions when mentions are disabled', function () {
    config()->set('comments.mentions.enabled', false);

    Event::fake([UserMentioned::class]);

    $user = User::factory()->create();
    User::factory()->create(['name' => 'john']);
    $post = Post::factory()->create();

    $comment = Comment::factory()->create([
        'commentable_id' => $post->id,
        'commentable_type' => $post->getMorphClass(),
        'commenter_id' => $user->getKey(),
        'commenter_type' => $user->getMorphClass(),
        'body' => '<p>Hello @john</p>',
    ]);

    app(MentionParser::class)->syncMentions($comment);

    expect($comment->mentions()->count())->toBe(0);
    Event::assertNotDispatched(UserMentioned::class);
});

it('attaches the mention provider to the editor when enabled', function () {
    $editor = CommentsConfig::applyMentionProvider(RichEditor::make('body'));

    expect($editor->hasMentions())->toBeTrue();
});

it('does not attach the mention provider to the editor when disabled', function () {
    config()->set('comments.mentions.enabled', false);

    $editor = CommentsConfig::applyMentionProvider(RichEditor::make('body'));

    expect($editor->hasMentions())->toBeFalse();
});
