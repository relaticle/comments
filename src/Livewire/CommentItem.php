<?php

namespace Relaticle\Comments\Livewire;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Relaticle\Comments\CommentsConfig;
use Relaticle\Comments\Events\CommentCreated;
use Relaticle\Comments\Events\CommentDeleted;
use Relaticle\Comments\Events\CommentUpdated;
use Relaticle\Comments\Mentions\MentionParser;
use Relaticle\Comments\Models\Comment;

class CommentItem extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;
    use WithFileUploads;

    public Comment $comment;

    public bool $isEditing = false;

    public bool $isReplying = false;

    /** @var array<string, mixed> */
    public ?array $editData = [];

    /** @var array<string, mixed> */
    public ?array $replyData = [];

    /** @var array<int, TemporaryUploadedFile> */
    public array $replyAttachments = [];

    public function mount(Comment $comment): void
    {
        $this->comment = $comment;
    }

    public function editForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                RichEditor::make('body')
                    ->hiddenLabel()
                    ->required()
                    ->placeholder(__('comments::comments.placeholder_edit'))
                    ->toolbarButtons(CommentsConfig::getEditorToolbar())
                    ->mentions([
                        CommentsConfig::makeMentionProvider(),
                    ]),
            ])
            ->statePath('editData');
    }

    public function replyForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                RichEditor::make('body')
                    ->hiddenLabel()
                    ->required()
                    ->placeholder(__('comments::comments.placeholder_reply'))
                    ->toolbarButtons(CommentsConfig::getEditorToolbar())
                    ->mentions([
                        CommentsConfig::makeMentionProvider(),
                    ]),
            ])
            ->statePath('replyData');
    }

    public function startEdit(): void
    {
        $this->authorize('update', $this->comment);

        $this->isEditing = true;

        $body = $this->comment->body;

        foreach ($this->comment->attachments as $attachment) {
            if ($attachment->isImage()) {
                $body .= '<img src="'.e($attachment->url()).'" alt="'.e($attachment->original_name).'">';
            }
        }

        $this->editForm->fill(['body' => $body]);
    }

    public function cancelEdit(): void
    {
        $this->isEditing = false;
        $this->editForm->fill();
    }

    public function saveEdit(): void
    {
        $this->authorize('update', $this->comment);

        $data = $this->editForm->getState();
        $body = $data['body'] ?? '';

        foreach ($this->comment->attachments as $attachment) {
            if ($attachment->isImage()) {
                $escapedUrl = preg_quote(e($attachment->url()), '/');
                $body = preg_replace('/<img[^>]*src=["\']'.$escapedUrl.'["\'][^>]*\/?>/i', '', $body);
            }
        }

        $this->comment->update([
            'body' => $body,
            'edited_at' => now(),
        ]);

        event(new CommentUpdated($this->comment->fresh()));

        app(MentionParser::class)->syncMentions($this->comment->fresh());

        $this->dispatch('commentUpdated');

        $this->isEditing = false;
        $this->editForm->fill();
    }

    public function deleteComment(): void
    {
        $this->authorize('delete', $this->comment);

        $this->comment->delete();

        event(new CommentDeleted($this->comment));

        $this->dispatch('commentDeleted');
    }

    public function startReply(): void
    {
        if (! $this->comment->canReply()) {
            return;
        }

        $this->isReplying = true;
        $this->replyForm->fill();
    }

    public function cancelReply(): void
    {
        $this->isReplying = false;
        $this->replyForm->fill();
        $this->replyAttachments = [];
    }

    public function addReply(): void
    {
        $this->authorize('reply', $this->comment);

        $data = $this->replyForm->getState();

        if (CommentsConfig::areAttachmentsEnabled()) {
            $maxSize = CommentsConfig::getAttachmentMaxSize();
            $allowedTypes = implode(',', CommentsConfig::getAttachmentAllowedTypes());
            $this->validate([
                'replyAttachments.*' => ['nullable', 'file', "max:{$maxSize}", "mimetypes:{$allowedTypes}"],
            ]);
        }

        $user = CommentsConfig::resolveAuthenticatedUser();

        $reply = $this->comment->commentable->comments()->create([
            'body' => $data['body'] ?? '',
            'parent_id' => $this->comment->id,
            'commenter_id' => $user->getKey(),
            'commenter_type' => $user->getMorphClass(),
        ]);

        if (CommentsConfig::areAttachmentsEnabled() && ! empty($this->replyAttachments)) {
            $disk = CommentsConfig::getAttachmentDisk();

            foreach ($this->replyAttachments as $file) {
                $path = $file->store("comments/attachments/{$reply->id}", $disk);

                $reply->attachments()->create([
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'disk' => $disk,
                ]);
            }
        }

        event(new CommentCreated($reply));

        app(MentionParser::class)->syncMentions($reply);

        $this->comment->load(['replies.commenter', 'replies.mentions', 'replies.attachments', 'replies.reactions.commenter', 'replies.replies.commenter', 'replies.replies.mentions', 'replies.replies.attachments', 'replies.replies.reactions.commenter']);

        $this->dispatch('commentUpdated');

        $this->isReplying = false;
        $this->replyForm->fill();
        $this->replyAttachments = [];
    }

    public function removeReplyAttachment(int $index): void
    {
        $attachments = $this->replyAttachments;
        unset($attachments[$index]);
        $this->replyAttachments = array_values($attachments);
    }

    public function render(): View
    {
        return view('comments::livewire.comment-item');
    }
}
