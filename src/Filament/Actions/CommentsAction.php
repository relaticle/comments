<?php

namespace Relaticle\Comments\Filament\Actions;

use Filament\Actions\Action;
use Illuminate\Contracts\View\View;
use Relaticle\Comments\Concerns\HasComments;

class CommentsAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('Comments'))
            ->icon('heroicon-o-chat-bubble-left-right')
            ->slideOver()
            ->modalHeading(__('Comments'))
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->modalContent(function (): View {
                return view('comments::filament.comments-action', [
                    'record' => $this->getRecord(),
                ]);
            })
            ->badge(function ($record): ?int {
                if (! $record) {
                    return null;
                }

                if (! in_array(HasComments::class, class_uses_recursive($record))) {
                    return null;
                }

                $count = $record->commentCount();

                return $count > 0 ? $count : null;
            })
            ->badgeColor('gray');
    }

    public static function getDefaultName(): ?string
    {
        return 'comments';
    }
}
