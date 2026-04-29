<div class="flex gap-3 mb-8" wire:key="comment-item-{{ $comment->id }}">
    {{-- Avatar --}}
    <div class="shrink-0">
        @if ($comment->trashed())
            <div class="h-8 w-8 rounded-full bg-gray-200 dark:bg-gray-700"></div>
        @elseif ($comment->commenter?->getCommentAvatarUrl())
            <img src="{{ $comment->commenter->getCommentAvatarUrl() }}" alt="{{ $comment->commenter->getCommentDisplayName() }}" class="h-8 w-8 rounded-full object-cover">
        @else
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-100 text-sm font-medium text-primary-700 dark:bg-primary-800 dark:text-primary-300">
                {{ str($comment->commenter?->getCommentDisplayName() ?? '?')->substr(0, 1)->upper() }}
            </div>
        @endif
    </div>

    <div class="min-w-0 flex-1">
        {{-- Deleted placeholder --}}
        @if ($comment->trashed())
            <p class="text-sm italic text-gray-400 dark:text-gray-500">This comment has been deleted</p>
        @else
            {{-- Header: name + timestamp --}}
            <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    {{ $comment->commenter?->getCommentDisplayName() ?? 'Unknown' }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400" title="{{ $comment->created_at->format('M j, Y g:i A') }}">
                    {{ $comment->created_at->diffForHumans() }}
                </span>
                @if ($comment->isEdited())
                    <span class="text-xs text-gray-400 dark:text-gray-500">(edited)</span>
                @endif
            </div>

            {{-- Body or edit form --}}
            @if ($isEditing)
                <div class="mt-1">
                    {{ $this->editForm }}

                    @php $fileAttachments = $comment->attachments->filter(fn($a) => !$a->isImage()) @endphp
                    @if ($fileAttachments->isNotEmpty())
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($fileAttachments as $attachment)
                                <a href="{{ $attachment->url() }}" target="_blank" rel="noopener noreferrer" download="{{ $attachment->original_name }}"
                                    class="flex items-center gap-2 rounded border border-gray-200 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                                    <svg class="h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                    <span class="truncate">{{ $attachment->original_name }}</span>
                                    <span class="shrink-0 text-xs text-gray-400 dark:text-gray-500">({{ $attachment->formattedSize() }})</span>
                                </a>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-2 flex items-center justify-between">
                        <button type="button" wire:click="cancelEdit" class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400">Cancel</button>
                        <button type="button" wire:click="saveEdit" class="text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400">Save</button>
                    </div>
                </div>
            @else
                <div class="fi-prose prose prose-sm mt-1 max-w-none text-gray-700 dark:prose-invert dark:text-gray-300">
                    {!! $comment->renderBodyWithMentions() !!}
                </div>

                {{-- Attachments --}}
                @if ($comment->attachments->isNotEmpty())
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach ($comment->attachments as $attachment)
                            @if ($attachment->isImage())
                                <a href="{{ $attachment->url() }}" target="_blank" rel="noopener noreferrer" class="block">
                                    <img src="{{ $attachment->url() }}" alt="{{ $attachment->original_name }}"
                                        class="max-h-[200px] rounded border border-gray-200 object-cover dark:border-gray-600" />
                                </a>
                            @else
                                <a href="{{ $attachment->url() }}" target="_blank" rel="noopener noreferrer" download="{{ $attachment->original_name }}"
                                    class="flex items-center gap-2 rounded border border-gray-200 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                                    <svg class="h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                    <span class="truncate">{{ $attachment->original_name }}</span>
                                    <span class="shrink-0 text-xs text-gray-400 dark:text-gray-500">({{ $attachment->formattedSize() }})</span>
                                </a>
                            @endif
                        @endforeach
                    </div>
                @endif

                {{-- Reactions --}}
                <livewire:reactions :comment="$comment" :key="'reactions-'.$comment->id" />
            @endif

            {{-- Actions: Reply, Edit, Delete --}}
            <div class="mt-2 flex items-center gap-3">
                @auth
                    @if ($comment->canReply())
                        @can('reply', $comment)
                            <button wire:click="startReply" type="button"
                                class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                Reply
                            </button>
                        @endcan
                    @endif

                    @can('update', $comment)
                        <button wire:click="startEdit" type="button"
                            class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            Edit
                        </button>
                    @endcan

                    @can('delete', $comment)
                        <button wire:click="deleteComment"
                            wire:confirm="Are you sure you want to delete this comment?"
                            type="button"
                            class="text-xs text-danger-600 hover:text-danger-500 dark:text-danger-400 dark:hover:text-danger-300">
                            Delete
                        </button>
                    @endcan

                    @php
                        $authenticatedUser = \Relaticle\Comments\CommentsConfig::resolveAuthenticatedUser();
                    @endphp
                    @if ($authenticatedUser && \Relaticle\Comments\CommentsConfig::canPin($authenticatedUser, $comment))
                        @if ($comment->isPinned())
                            <button wire:click="$parent.unpinComment({{ $comment->id }})" type="button"
                                class="text-xs text-amber-600 hover:text-amber-500 dark:text-amber-400 dark:hover:text-amber-300">
                                Unpin
                            </button>
                        @else
                            <button wire:click="$parent.pinComment({{ $comment->id }})" type="button"
                                class="text-xs text-gray-400 hover:text-amber-500 dark:text-gray-500 dark:hover:text-amber-400">
                                Pin
                            </button>
                        @endif
                    @endif
                @endauth
            </div>
        @endif

        {{-- Reply form --}}
        @if ($isReplying)
            <div class="mt-3"
                x-data="{ uploadError: null }"
                x-on:livewire-upload-error.window="uploadError = '{{ __('File upload failed. The file may be too large or an unsupported type.') }}'"
                x-on:livewire-upload-start.window="uploadError = null"
            >
                {{ $this->replyForm }}

                @if (!empty($replyAttachments))
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach ($replyAttachments as $index => $file)
                            <div class="flex items-center gap-1 rounded bg-gray-100 px-2 py-1 text-xs text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                <span>{{ $file->getClientOriginalName() }}</span>
                                <button type="button" wire:click="removeReplyAttachment({{ $index }})" class="text-gray-400 hover:text-danger-500 dark:text-gray-500 dark:hover:text-danger-400">&times;</button>
                            </div>
                        @endforeach
                    </div>

                    @error('replyAttachments.*')
                        <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                    <p x-show="uploadError" x-text="uploadError" class="mt-1 text-sm text-danger-600 dark:text-danger-400"></p>
                @endif

                <div class="mt-2 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        @if (\Relaticle\Comments\CommentsConfig::areAttachmentsEnabled())
                            <label class="flex cursor-pointer items-center gap-1.5 text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" />
                                </svg>
                                Attach
                                <input type="file" wire:model="replyAttachments" multiple class="hidden" accept="{{ implode(',', \Relaticle\Comments\CommentsConfig::getAttachmentAllowedTypes()) }}" />
                            </label>
                        @endif
                        <button type="button" wire:click="cancelReply" class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400">Cancel</button>
                    </div>
                    <button type="button" wire:click="addReply" class="text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400">Reply</button>
                </div>
            </div>
        @endif

        {{-- Nested replies --}}
        @if ($comment->relationLoaded('replies') && $comment->replies->isNotEmpty())
            <div class="mt-3 space-y-3 border-l border-gray-200 pl-4 dark:border-gray-700">
                @foreach ($comment->replies as $reply)
                    <livewire:comment-item :comment="$reply" :key="'comment-'.$reply->id" />
                @endforeach
            </div>
        @endif
    </div>
</div>
