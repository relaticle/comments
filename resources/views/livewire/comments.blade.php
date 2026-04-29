<div class="comments-wrap"
    @if (!\Relaticle\Comments\CommentsConfig::isBroadcastingEnabled())
        wire:poll.{{ \Relaticle\Comments\CommentsConfig::getPollingInterval() }}
    @endif
    x-data="{ uploadError: null }"
    x-on:livewire-upload-error.window="uploadError = '{{ __('File upload failed. The file may be too large or an unsupported type.') }}'"
    x-on:livewire-upload-start.window="uploadError = null"
>
    <div class="comments-body space-y-4">
    {{-- Sort toggle --}}
    <div class="flex items-center justify-between">
        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">
            Comments ({{ $this->allCommentsCount }})
        </h3>
        @auth
            <div class="flex items-center gap-3">
                <button wire:click="toggleSubscription" type="button"
                    class="flex items-center gap-1 text-xs {{ $this->isSubscribed ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400 dark:text-gray-500' }} hover:text-primary-500">
                    @if ($this->isSubscribed)
                        {{-- Bell icon (solid) --}}
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 2a6 6 0 00-6 6c0 1.887-.454 3.665-1.257 5.234a.75.75 0 00.515 1.076 32.91 32.91 0 003.256.508 3.5 3.5 0 006.972 0 32.903 32.903 0 003.256-.508.75.75 0 00.515-1.076A11.448 11.448 0 0116 8a6 6 0 00-6-6zm0 14.5a2 2 0 01-1.95-1.557 33.146 33.146 0 003.9 0A2 2 0 0110 16.5z" clip-rule="evenodd"/>
                        </svg>
                        Subscribed
                    @else
                        {{-- Bell icon (outline) --}}
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                        </svg>
                        Subscribe
                    @endif
                </button>
                <button wire:click="toggleSort" type="button"
                    class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    {{ $sortDirection === 'asc' ? 'Oldest first' : 'Newest first' }}
                </button>
            </div>
        @else
            <button wire:click="toggleSort" type="button"
                class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                {{ $sortDirection === 'asc' ? 'Oldest first' : 'Newest first' }}
            </button>
        @endauth
    </div>

    {{-- Pinned comments --}}
    @if (\Relaticle\Comments\CommentsConfig::isPinningEnabled() && $this->pinnedComments->isNotEmpty())
        <div class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
            {{-- Header --}}
            <div class="flex items-center gap-2 border-b border-gray-100 px-4 py-2.5 dark:border-gray-700">
                <svg class="h-3.5 w-3.5 text-amber-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd" d="M6.32 2.577a49.255 49.255 0 0 1 11.36 0c1.497.174 2.57 1.46 2.57 2.93V21a.75.75 0 0 1-1.085.67L12 18.089l-7.165 3.583A.75.75 0 0 1 3.75 21V5.507c0-1.47 1.073-2.756 2.57-2.93Z" clip-rule="evenodd"/>
                </svg>
                <span class="text-xs font-semibold text-gray-600 dark:text-gray-300">Pinned</span>
            </div>
            {{-- Pinned comment list with dividers --}}
            <div class="divide-y divide-gray-100 px-4 dark:divide-gray-700">
                @foreach ($this->pinnedComments as $comment)
                    <div class="py-4">
                        <livewire:comment-item :comment="$comment" :key="'pinned-'.$comment->id" />
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Comment list --}}
    <div class="space-y-4">
        @foreach ($this->comments as $comment)
            <livewire:comment-item :comment="$comment" :key="'comment-'.$comment->id" />
        @endforeach
    </div>

    {{-- Load more button --}}
    @if ($this->hasMore)
        <div class="text-center">
            <button wire:click="loadMore" type="button"
                class="text-sm text-primary-600 hover:text-primary-500 dark:text-primary-400">
                Load more comments
                <span wire:loading wire:target="loadMore" class="ml-1">...</span>
            </button>
        </div>
    @endif

    </div>{{-- end comments-body --}}

    {{-- New comment form - sticky at bottom of slide-over --}}
    @auth
        @can('create', \Relaticle\Comments\CommentsConfig::getCommentModel())
            <div class="shrink-0 border-t border-gray-200 bg-white px-6 pt-3 dark:border-gray-700 dark:bg-gray-900 -mx-6">                {{ $this->commentForm }}

                @if (!empty($attachments))
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach ($attachments as $index => $file)
                            <div class="flex items-center gap-1 rounded bg-gray-100 px-2 py-1 text-xs text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                <span>{{ $file->getClientOriginalName() }}</span>
                                <button type="button" wire:click="removeAttachment({{ $index }})" class="text-gray-400 hover:text-danger-500 dark:text-gray-500 dark:hover:text-danger-400">&times;</button>
                            </div>
                        @endforeach
                    </div>

                    @error('attachments.*')
                        <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                    <p x-show="uploadError" x-text="uploadError" class="mt-1 text-sm text-danger-600 dark:text-danger-400"></p>
                @endif

                <div class="mt-2 flex items-center justify-between">
                    @if (\Relaticle\Comments\CommentsConfig::areAttachmentsEnabled())
                        <label class="flex cursor-pointer items-center gap-1.5 text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" />
                            </svg>
                            Attach
                            <input type="file" wire:model="attachments" multiple class="hidden" accept="{{ implode(',', \Relaticle\Comments\CommentsConfig::getAttachmentAllowedTypes()) }}" />
                        </label>
                    @else
                        <div></div>
                    @endif

                    <button type="button" wire:click="addComment"
                        class="inline-flex items-center rounded-lg bg-primary-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:bg-primary-500 dark:hover:bg-primary-400 dark:focus:ring-offset-gray-800"
                        wire:loading.attr="disabled" wire:target="addComment">
                        <span wire:loading.remove wire:target="addComment">Comment</span>
                        <span wire:loading wire:target="addComment">Posting...</span>
                    </button>
                </div>
            </div>
        @endcan
    @endauth
</div>
