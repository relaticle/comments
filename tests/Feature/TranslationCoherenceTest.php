<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Lang;
use Livewire\Livewire;
use Relaticle\Comments\Filament\Actions\CommentsAction;
use Relaticle\Comments\Livewire\Comments;
use Relaticle\Comments\Livewire\Reactions;
use Relaticle\Comments\Notifications\CommentRepliedNotification;
use Relaticle\Comments\Notifications\UserMentionedNotification;
use Relaticle\Comments\Tests\Models\Post;
use Relaticle\Comments\Tests\Models\User;

beforeEach(function (): void {
    Lang::addLines([
        'comments.placeholder' => 'OVERRIDDEN_PLACEHOLDER',
        'comments.placeholder_reply' => 'OVERRIDDEN_REPLY_PLACEHOLDER',
        'comments.placeholder_edit' => 'OVERRIDDEN_EDIT_PLACEHOLDER',
        'comments.title' => 'OVERRIDDEN_TITLE',
        'comments.load_more' => 'OVERRIDDEN_LOAD_MORE',
        'comments.posting' => 'OVERRIDDEN_POSTING',
        'comments.submit' => 'OVERRIDDEN_SUBMIT',
        'comments.pinned' => 'OVERRIDDEN_PINNED',
        'comments.edited_marker' => 'OVERRIDDEN_EDITED_MARKER',
        'comments.deleted_inline' => 'OVERRIDDEN_DELETED_INLINE',
        'comments.actions.reply' => 'OVERRIDDEN_REPLY',
        'comments.actions.edit' => 'OVERRIDDEN_EDIT',
        'comments.actions.delete' => 'OVERRIDDEN_DELETE',
        'comments.actions.cancel' => 'OVERRIDDEN_CANCEL',
        'comments.actions.save' => 'OVERRIDDEN_SAVE',
        'comments.actions.pin' => 'OVERRIDDEN_PIN',
        'comments.actions.unpin' => 'OVERRIDDEN_UNPIN',
        'comments.actions.confirm_delete' => 'OVERRIDDEN_CONFIRM_DELETE',
        'comments.subscriptions.subscribe_short' => 'OVERRIDDEN_SUBSCRIBE_SHORT',
        'comments.subscriptions.subscribed_short' => 'OVERRIDDEN_SUBSCRIBED_SHORT',
        'comments.attachments.attach' => 'OVERRIDDEN_ATTACH',
        'comments.attachments.upload_failed' => 'OVERRIDDEN_UPLOAD_FAILED',
        'comments.reactions.like' => 'OVERRIDDEN_LIKE',
        'comments.reactions.add_reaction' => 'OVERRIDDEN_ADD_REACTION',
        'comments.reactions.and_others' => 'OVERRIDDEN_AND_:count_OTHERS',
        'comments.notifications.reply_subject' => 'OVERRIDDEN_REPLY_SUBJECT',
        'comments.notifications.reply_body' => 'OVERRIDDEN_REPLY_BODY_BY_:name',
        'comments.notifications.mention_subject' => 'OVERRIDDEN_MENTION_SUBJECT',
        'comments.notifications.mention_body' => 'OVERRIDDEN_MENTION_BODY_BY_:name',
    ], 'en', 'comments');
});

it('resolves comments::* keys from package lang files (without addLines)', function (): void {
    // Bypass the addLines override registered in beforeEach — flush the namespace.
    app('translator')->setLoaded([]);

    expect(__('comments::comments.placeholder'))->not->toStartWith('comments::');
    expect(__('comments::comments.title'))->not->toStartWith('comments::');
    expect(__('comments::comments.actions.reply'))->not->toStartWith('comments::');
    expect(__('comments::comments.reactions.like'))->not->toStartWith('comments::');
    expect(__('comments::comments.subscriptions.subscribe_short'))->not->toStartWith('comments::');
    expect(__('comments::comments.attachments.attach'))->not->toStartWith('comments::');
    expect(__('comments::comments.notifications.reply_subject'))->not->toStartWith('comments::');
});

it('uses the comments translation namespace for the new-comment placeholder', function (): void {
    $post = Post::factory()->create();
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(Comments::class, ['model' => $post])
        ->assertSee('OVERRIDDEN_PLACEHOLDER');
});

it('uses the comments translation namespace for hardcoded blade strings', function (): void {
    $post = Post::factory()->create();
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(Comments::class, ['model' => $post])
        ->assertSee('OVERRIDDEN_TITLE')
        ->assertSee('OVERRIDDEN_SUBSCRIBE_SHORT')
        ->assertSee('OVERRIDDEN_SUBMIT')
        ->assertSee('OVERRIDDEN_ATTACH');
});

it('uses the comments translation namespace for the comments action label and heading', function (): void {
    $action = CommentsAction::make();

    expect($action->getLabel())->toBe('OVERRIDDEN_TITLE')
        ->and($action->getModalHeading())->toBe('OVERRIDDEN_TITLE');
});

it('uses the comments translation namespace for the file-upload-failed message', function (): void {
    $post = Post::factory()->create();
    $user = User::factory()->create();
    $this->actingAs($user);

    $html = Livewire::test(Comments::class, ['model' => $post])->html();

    expect($html)->toContain('OVERRIDDEN_UPLOAD_FAILED');
});

it('uses the comments translation namespace for the reply notification email', function (): void {
    $post = Post::factory()->create();
    $author = User::factory()->create(['name' => 'Author']);
    $replier = User::factory()->create(['name' => 'Replier']);

    $parent = $post->comments()->create([
        'body' => 'parent',
        'commenter_id' => $author->getKey(),
        'commenter_type' => $author->getMorphClass(),
    ]);
    $reply = $post->comments()->create([
        'body' => 'reply body',
        'parent_id' => $parent->id,
        'commenter_id' => $replier->getKey(),
        'commenter_type' => $replier->getMorphClass(),
    ]);

    $mail = (new CommentRepliedNotification($reply))->toMail($author);

    expect($mail->subject)->toBe('OVERRIDDEN_REPLY_SUBJECT')
        ->and($mail->introLines)->toContain('OVERRIDDEN_REPLY_BODY_BY_Replier');
});

it('uses the comments translation namespace for the mention notification email', function (): void {
    $post = Post::factory()->create();
    $mentioner = User::factory()->create(['name' => 'Mentioner']);
    $mentioned = User::factory()->create(['name' => 'Mentioned']);

    $comment = $post->comments()->create([
        'body' => 'hi',
        'commenter_id' => $mentioner->getKey(),
        'commenter_type' => $mentioner->getMorphClass(),
    ]);

    $mail = (new UserMentionedNotification($comment, $mentioner))->toMail($mentioned);

    expect($mail->subject)->toBe('OVERRIDDEN_MENTION_SUBJECT')
        ->and($mail->introLines)->toContain('OVERRIDDEN_MENTION_BODY_BY_Mentioner');
});

it('uses the comments translation namespace for the reaction summary "and X more" suffix', function (): void {
    $post = Post::factory()->create();
    $author = User::factory()->create(['name' => 'Author']);
    $this->actingAs($author);

    $comment = $post->comments()->create([
        'body' => 'hi',
        'commenter_id' => $author->getKey(),
        'commenter_type' => $author->getMorphClass(),
    ]);

    foreach (range(1, 5) as $i) {
        $u = User::factory()->create(['name' => "User{$i}"]);
        $comment->reactions()->create([
            'commenter_id' => $u->getKey(),
            'commenter_type' => $u->getMorphClass(),
            'reaction' => 'thumbs_up',
        ]);
    }

    $html = Livewire::test(Reactions::class, ['comment' => $comment->fresh()])->html();

    expect($html)->toContain('OVERRIDDEN_AND_2_OTHERS');
});
