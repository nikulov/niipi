<?php

namespace App\Services;

use App\Enums\ProjectStatus;
use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

final class ProjectsQuery
{
    public function list(
        int $perPageOrLimit = 4,
        ?array $categoryIds = null,
        bool $paginate = false,
        string $pageName = 'page'
    ): Collection|LengthAwarePaginator {
        $query = Project::query()
            ->with('categories')
            ->where('status', ProjectStatus::Published)
            ->orderByDesc('published_at');
        
        if ($categoryIds && $categoryIds !== []) {
            $query->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $categoryIds));
        }
        
        if (!$paginate) {
            return $query->limit($perPageOrLimit)->get();
        }
        
        return $query->paginate($perPageOrLimit, ['*'], $pageName)->withQueryString();
    }
}