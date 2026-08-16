<?php

namespace Relaticle\Comments\Mentions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Relaticle\Comments\CommentsConfig;
use Relaticle\Comments\Contracts\MentionResolver;

class DefaultMentionResolver implements MentionResolver
{
    /** @return Collection<int, Model> */
    public function search(string $query): Collection
    {
        $model = CommentsConfig::getCommenterModel();
        $builder = $model::query();
        $columns = CommentsConfig::getMentionSearchColumns();

        foreach ($columns as $index => $column) {
            $method = $index === 0 ? 'where' : 'orWhere';
            $builder->{$method}($column, 'like', "%{$query}%");
        }

        return $builder
            ->orderBy($columns[0])
            ->limit(CommentsConfig::getMentionMaxResults())
            ->get();
    }

    /** @return Collection<int, Model> */
    public function resolveByNames(array $names): Collection
    {
        $model = CommentsConfig::getCommenterModel();
        $nameColumn = CommentsConfig::getMentionNameColumn();

        return $model::query()
            ->whereIn($nameColumn, $names)
            ->get();
    }
}
