<?php

namespace Relaticle\Comments\Livewire;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Relaticle\Comments\CommentsConfig;
use Relaticle\Comments\Events\CommentCreated;
use Relaticle\Comments\Events\CommentPinned;
use Relaticle\Comments\Events\CommentUnpinned;
use Relaticle\Comments\Mentions\MentionParser;
use Relaticle\Comments\Models\Comment;
use Relaticle\Comments\Models\Subscription;

class Comments extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;
    use WithFileUploads;

    public Model $model;

    /** @var array<string, mixed> */
    public ?array $commentData = [];

    public string $sortDirection = 'asc';

    /** @var array<int, TemporaryUploadedFile> */
    public array $attachments = [];

    public int $perPage = 10;

    public int $loadedCount = 10;

    public function mount(Model $model): void
    {
        $this->model = $model;
        $this->perPage = CommentsConfig::getPerPage();
        $this->loadedCount = $this->perPage;
        $this->commentForm->fill();
    }

    public function commentForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                CommentsConfig::applyMentionProvider(
                    RichEditor::make('body')
                        ->hiddenLabel()
                        ->required()
                        ->placeholder(__('comments::comments.placeholder'))
                        ->toolbarButtons(CommentsConfig::getEditorToolbar())
                ),
            ])
            ->statePath('commentData');
    }

    /** @return Collection<int, Comment> */
    #[Computed]
    public function pinnedComments(): Collection
    {
        if (! CommentsConfig::isPinningEnabled()) {
            return new Collection;
        }

        return $this->model
            ->topLevelComments()
            ->pinned()
            ->with(['commenter', 'mentions', 'attachments', 'reactions.commenter', 'replies.commenter', 'replies.mentions', 'replies.attachments', 'replies.reactions.commenter', 'replies.replies.commenter', 'replies.replies.mentions', 'replies.replies.attachments', 'replies.replies.reactions.commenter'])
            ->get();
    }

    /** @return Collection<int, Comment> */
    #[Computed]
    public function comments(): Collection
    {
        return $this->model
            ->topLevelComments()
            ->unpinned()
            ->with(['commenter', 'mentions', 'attachments', 'reactions.commenter', 'replies.commenter', 'replies.mentions', 'replies.attachments', 'replies.reactions.commenter', 'replies.replies.commenter', 'replies.replies.mentions', 'replies.replies.attachments', 'replies.replies.reactions.commenter'])
            ->orderBy('created_at', $this->sortDirection)
            ->take($this->loadedCount)
            ->get();
    }

    #[Computed]
    public function totalCount(): int
    {
        return $this->model->topLevelComments()->unpinned()->count();
    }

    #[Computed]
    public function allCommentsCount(): int
    {
        return $this->model->commentCount();
    }

    #[Computed]
    public function hasMore(): bool
    {
        return $this->totalCount > $this->loadedCount;
    }

    #[Computed]
    public function isSubscribed(): bool
    {
        $user = CommentsConfig::resolveAuthenticatedUser();

        if (! $user) {
            return false;
        }

        return Subscription::isSubscribed($this->model, $user);
    }

    public function toggleSubscription(): void
    {
        $user = CommentsConfig::resolveAuthenticatedUser();

        if (! $user) {
            return;
        }

        if ($this->isSubscribed) {
            Subscription::unsubscribe($this->model, $user);
        } else {
            Subscription::subscribe($this->model, $user);
        }

        unset($this->isSubscribed);
    }

    public function addComment(): void
    {
        $data = $this->commentForm->getState();

        if (CommentsConfig::areAttachmentsEnabled()) {
            $maxSize = CommentsConfig::getAttachmentMaxSize();
            $allowedTypes = implode(',', CommentsConfig::getAttachmentAllowedTypes());
            $this->validate([
                'attachments.*' => ['nullable', 'file', "max:{$maxSize}", "mimetypes:{$allowedTypes}"],
            ]);
        }

        $this->authorize('create', CommentsConfig::getCommentModel());

        $user = CommentsConfig::resolveAuthenticatedUser();

        $comment = $this->model->comments()->create([
            'body' => $data['body'] ?? '',
            'commenter_id' => $user->getKey(),
            'commenter_type' => $user->getMorphClass(),
        ]);

        if (CommentsConfig::areAttachmentsEnabled() && ! empty($this->attachments)) {
            $disk = CommentsConfig::getAttachmentDisk();

            foreach ($this->attachments as $file) {
                $path = $file->store("comments/attachments/{$comment->id}", $disk);

                $comment->attachments()->create([
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'disk' => $disk,
                ]);
            }
        }

        event(new CommentCreated($comment));

        app(MentionParser::class)->syncMentions($comment);

        $this->commentForm->fill();
        $this->reset('attachments');
    }

    public function removeAttachment(int $index): void
    {
        $attachments = $this->attachments;
        unset($attachments[$index]);
        $this->attachments = array_values($attachments);
    }

    public function loadMore(): void
    {
        $this->loadedCount += $this->perPage;
    }

    public function toggleSort(): void
    {
        $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
    }

    /** @return array<string, string> */
    public function getListeners(): array
    {
        $listeners = [
            'commentDeleted' => 'refreshComments',
            'commentUpdated' => 'refreshComments',
        ];

        if (CommentsConfig::isBroadcastingEnabled()) {
            $prefix = CommentsConfig::getBroadcastChannelPrefix();
            $type = $this->model->getMorphClass();
            $id = $this->model->getKey();
            $channel = "echo-private:{$prefix}.{$type}.{$id}";

            $listeners["{$channel},CommentCreated"] = 'refreshComments';
            $listeners["{$channel},CommentUpdated"] = 'refreshComments';
            $listeners["{$channel},CommentDeleted"] = 'refreshComments';
            $listeners["{$channel},CommentReacted"] = 'refreshComments';
            $listeners["{$channel},CommentPinned"] = 'refreshComments';
            $listeners["{$channel},CommentUnpinned"] = 'refreshComments';
        }

        return $listeners;
    }

    public function pinComment(int $commentId): void
    {
        $comment = $this->model->topLevelComments()->find($commentId);
        $user = CommentsConfig::resolveAuthenticatedUser();

        if (! $comment) {
            return;
        }

        if (! $user || ! CommentsConfig::canPin($user, $comment)) {
            return;
        }

        $maxPinned = CommentsConfig::getMaxPinned();

        if ($maxPinned !== null && $this->model->topLevelComments()->pinned()->count() >= $maxPinned) {
            return;
        }

        $comment->pin();
        event(new CommentPinned($comment));
        $this->refreshComments();
    }

    public function unpinComment(int $commentId): void
    {
        $comment = $this->model->topLevelComments()->find($commentId);
        $user = CommentsConfig::resolveAuthenticatedUser();

        if (! $comment) {
            return;
        }

        if (! $user || ! CommentsConfig::canPin($user, $comment)) {
            return;
        }

        $comment->unpin();
        event(new CommentUnpinned($comment));
        $this->refreshComments();
    }

    public function refreshComments(): void
    {
        unset($this->comments, $this->pinnedComments, $this->totalCount, $this->hasMore, $this->allCommentsCount);
    }

    public function render(): View
    {
        return view('comments::livewire.comments');
    }
}
