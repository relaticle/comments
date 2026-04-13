<?php

namespace Relaticle\Comments;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Relaticle\Comments\Contracts\MentionResolver;
use Relaticle\Comments\Events\CommentCreated;
use Relaticle\Comments\Events\UserMentioned;
use Relaticle\Comments\Http\Controllers\CommentsStyleController;
use Relaticle\Comments\Listeners\SendCommentRepliedNotification;
use Relaticle\Comments\Listeners\SendUserMentionedNotification;
use Relaticle\Comments\Livewire\CommentItem;
use Relaticle\Comments\Livewire\Comments;
use Relaticle\Comments\Livewire\Reactions;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class CommentsServiceProvider extends PackageServiceProvider
{
    public static string $name = 'comments';

    public static string $viewNamespace = 'comments';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile()
            ->hasViews(static::$viewNamespace)
            ->hasTranslations()
            ->hasMigrations([
                'create_comments_table',
                'create_comment_mentions_table',
                'create_comment_reactions_table',
                'create_comment_subscriptions_table',
                'create_comment_attachments_table',
            ]);
    }

    public function packageRegistered(): void
    {
        Relation::morphMap([
            'comment' => CommentsConfig::getCommentModel(),
        ]);

        $this->app->bind(
            MentionResolver::class,
            fn () => new (CommentsConfig::getMentionResolver())
        );

        $this->app->scoped(
            'comments.html_sanitizer',
            fn (): HtmlSanitizer => new HtmlSanitizer(
                (new HtmlSanitizerConfig)
                    ->allowSafeElements()
                    ->allowRelativeLinks()
                    ->allowRelativeMedias()
                    ->allowAttribute('class', allowedElements: '*')
                    ->allowAttribute('data-color', allowedElements: '*')
                    ->allowAttribute('data-from-breakpoint', allowedElements: '*')
                    ->allowAttribute('data-type', allowedElements: '*')
                    ->allowAttribute('data-id', allowedElements: 'span')
                    ->allowAttribute('data-label', allowedElements: 'span')
                    ->allowAttribute('data-char', allowedElements: 'span')
                    ->allowAttribute('style', allowedElements: '*')
                    ->withMaxInputLength(500000)
            ),
        );
    }

    public function packageBooted(): void
    {
        Gate::policy(
            CommentsConfig::getCommentModel(),
            CommentsConfig::getPolicyClass(),
        );

        Event::listen(CommentCreated::class, SendCommentRepliedNotification::class);
        Event::listen(UserMentioned::class, SendUserMentionedNotification::class);

        Livewire::component('comments', Comments::class);
        Livewire::component('comment-item', CommentItem::class);
        Livewire::component('reactions', Reactions::class);

        Route::get('/__relaticle-comments/css', CommentsStyleController::class);

        FilamentAsset::register([
            Css::make('comments')->html(
                static fn () => '<link rel="stylesheet" href="'.url('/__relaticle-comments/css').'" data-navigate-track />'
            ),
        ], 'relaticle/comments');
    }
}
