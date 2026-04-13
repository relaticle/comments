<div class="mt-1 flex flex-wrap items-center gap-1">
    {{-- Existing reactions with counts --}}
    @foreach ($this->reactionSummary as $summary)
        <button
            wire:click="toggleReaction('{{ $summary['reaction'] }}')"
            type="button"
            title="{{ implode(', ', $summary['names']) }}{{ $summary['total_reactors'] > 3 ? ' and ' . ($summary['total_reactors'] - 3) . ' more' : '' }}"
            class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-xs transition
                {{ $summary['reacted_by_user']
                    ? 'border-primary-300 bg-primary-50 text-primary-700 dark:border-primary-600 dark:bg-primary-900/30 dark:text-primary-300'
                    : 'border-gray-200 bg-gray-50 text-gray-600 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700' }}">
            <span>{{ $summary['emoji'] }}</span>
            <span>{{ $summary['count'] }}</span>
        </button>
    @endforeach

    @auth
        @php
            $thumbsUp = collect($this->reactionSummary)->firstWhere('reaction', 'thumbs_up');
            $hasLiked = $thumbsUp['reacted_by_user'] ?? false;
        @endphp

        {{-- Quick thumbs-up — same pill style as reactions, hidden once liked --}}
        @unless ($hasLiked)
            <button wire:click="toggleReaction('thumbs_up')" type="button" title="Like"
                    class="inline-flex items-center rounded-full border border-dashed border-gray-300 bg-transparent px-2 py-0.5 text-xs text-gray-400 transition hover:border-gray-400 hover:text-gray-500 dark:border-gray-600 dark:text-gray-500 dark:hover:border-gray-500 dark:hover:text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.633 10.25c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V2.75a.75.75 0 0 1 .75-.75 2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282m0 0h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23H5.904m10.598-9.75H14.25M5.904 18.5c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 0 1-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 9.953 4.167 9.5 5 9.5h1.053c.472 0 .745.556.5.96a8.958 8.958 0 0 0-1.302 4.665c0 1.194.232 2.333.654 3.375Z" />
                </svg>
            </button>
        @endunless

        {{-- Add reaction picker — same pill height, dashed border --}}
        <div class="relative max-h-[22px]" x-data="{ open: $wire.entangle('showPicker') }">
            <button @click="open = !open" type="button" title="Add reaction"
                    class="inline-flex items-center rounded-full border border-dashed border-gray-300 bg-transparent px-2 py-0.5 text-xs text-gray-400 transition hover:border-gray-400 hover:text-gray-500 dark:border-gray-600 dark:text-gray-500 dark:hover:border-gray-500 dark:hover:text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                <circle cx="12" cy="12" r="9"/>
                <path stroke-width="2.5" d="M9 9.5h.01M15 9.5h.01"/>
                <path d="M9 14.5a3 3 0 0 0 6 0"/>
            </svg>
        </button>
            <div x-show="open" x-cloak @click.outside="open = false"
                 class="absolute bottom-full left-0 z-50 mb-1 flex gap-1 rounded-lg border border-gray-200 bg-white p-2 shadow-lg dark:border-gray-600 dark:bg-gray-800">
                @foreach (\Relaticle\Comments\CommentsConfig::getReactionEmojiSet() as $key => $emoji)
                    <button wire:click="toggleReaction('{{ $key }}')" type="button"
                            class="rounded p-1 text-base hover:bg-gray-100 dark:hover:bg-gray-700"
                            title="{{ str_replace('_', ' ', $key) }}">
                        {{ $emoji }}
                    </button>
                @endforeach
            </div>

        </div>
    @endauth
</div>
