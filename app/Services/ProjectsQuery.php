<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

final class ProjectsQuery
{
    public function list(
        int $perPageOrLimit = 4,
        ?array $categoryIds = null,
        bool $paginate = false,
        string $pageName = 'page',
        int|array|null $excludeIds = null,
    ): Collection|LengthAwarePaginator {
        $query = Project::query()
            ->with('categories')
            ->published()
            ->ordered();

        if ($categoryIds && $categoryIds !== []) {
            $query->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $categoryIds));
        }

        $excludeIds = array_filter((array) $excludeIds, fn ($id) => $id !== null);

        if ($excludeIds !== []) {
            $query->whereNotIn('id', $excludeIds);
        }

        if (! $paginate) {
            return $query->limit($perPageOrLimit)->get();
        }

        return $query->paginate($perPageOrLimit, ['*'], $pageName)->withQueryString();
    }

    public function byIds(array $ids): Collection
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));

        if ($ids === []) {
            return new Collection;
        }

        $positions = array_flip($ids);

        return Project::query()
            ->with('categories')
            ->published()
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn (Project $project) => $positions[$project->getKey()])
            ->values();
    }
}
