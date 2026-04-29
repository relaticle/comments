<?php

namespace Relaticle\Comments\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Relaticle\Comments\CommentsConfig;
use Relaticle\Comments\Database\Factories\CommentFactory;
use Relaticle\Comments\Scopes\TenantScope;

class Comment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (self $comment): void {
            if (CommentsConfig::isMultiTenancyEnabled()) {
                $tenantId = CommentsConfig::resolveTenantId();

                if ($tenantId !== null) {
                    $comment->{CommentsConfig::getTenantColumn()} = $tenantId;
                }
            }
        });
    }

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (self $comment): void {
            $comment->body = app('comments.html_sanitizer')->sanitize($comment->body);
        });

        static::forceDeleting(function (self $comment): void {
            $comment->attachments()->delete();
            $comment->reactions()->delete();
            $comment->mentions()->detach();
        });
    }

    protected $fillable = [
        'body',
        'parent_id',
        'commenter_id',
        'commenter_type',
        'edited_at',
        'pinned_at',
    ];

    public function getTable(): string
    {
        return CommentsConfig::getCommentTable();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'edited_at' => 'datetime',
            'pinned_at' => 'datetime',
        ];
    }

    protected static function newFactory(): CommentFactory
    {
        return CommentFactory::new();
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function commenter(): MorphTo
    {
        return $this->morphTo();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(CommentsConfig::getCommentModel(), 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(CommentsConfig::getCommentModel(), 'parent_id');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function mentions(): MorphToMany
    {
        return $this->morphedByMany(
            CommentsConfig::getCommenterModel(),
            'commenter',
            CommentsConfig::getTableName('mentions'),
            'comment_id',
            'commenter_id',
        );
    }

    public function isReply(): bool
    {
        return $this->parent_id !== null;
    }

    public function isTopLevel(): bool
    {
        return $this->parent_id === null;
    }

    public function hasReplies(): bool
    {
        return $this->replies()->exists();
    }

    public function isEdited(): bool
    {
        return $this->edited_at !== null;
    }

    public function isPinned(): bool
    {
        return $this->pinned_at !== null;
    }

    public function pin(): void
    {
        $this->update(['pinned_at' => now()]);
    }

    public function unpin(): void
    {
        $this->update(['pinned_at' => null]);
    }

    /** @param Builder<static> $query */
    public function scopePinned(Builder $query): void
    {
        $query->whereNotNull('pinned_at')->orderBy('pinned_at', 'desc');
    }

    /** @param Builder<static> $query */
    public function scopeUnpinned(Builder $query): void
    {
        $query->whereNull('pinned_at');
    }

    public function canReply(): bool
    {
        return $this->depth() < CommentsConfig::getMaxDepth();
    }

    public function depth(): int
    {
        $depth = 0;
        $maxDepth = CommentsConfig::getMaxDepth();
        $parentId = $this->parent_id;

        while ($parentId !== null && $depth < $maxDepth) {
            $depth++;
            $parentId = static::where('id', $parentId)->value('parent_id');
        }

        return $depth;
    }

    public function renderBodyWithMentions(): string
    {
        $body = $this->body;

        $mentionsById = $this->mentions->keyBy('id');
        $handledIds = [];

        // Replace rich-editor mention spans by data-id (stable identifier, survives renames)
        $body = preg_replace_callback(
            '/<(?:span|a)[^>]*data-type="mention"[^>]*>[^<]*<\/(?:span|a)>/',
            function (array $matches) use ($mentionsById, &$handledIds): string {
                if (preg_match('/data-id=["\']([^"\']+)["\']/', $matches[0], $idMatch)) {
                    $id = $idMatch[1];
                    $user = $mentionsById->get($id);
                    if ($user !== null) {
                        $handledIds[] = $id;

                        return '<span class="comment-mention">@'.e($user->name).'</span>';
                    }
                }

                return $matches[0];
            },
            $body
        ) ?? $body;

        // Fallback for plain-text @Name mentions (no data-id span available)
        foreach ($this->mentions as $user) {
            if (in_array($user->getKey(), $handledIds)) {
                continue;
            }
            $styledSpan = '<span class="comment-mention">@'.e($user->name).'</span>';
            $body = str_replace('&#64;'.$user->name, $styledSpan, $body);
            $body = str_replace('@'.$user->name, $styledSpan, $body);
        }

        return $body;
    }
}
