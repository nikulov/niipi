<?php

namespace App\Presenters\Blocks;

use App\Models\Project;

final class ProjectsFullPresenter
{
    public static function make(Project $project): array
    {
        return [
            'title' => $project->title,
            'description' => $project->description,
            'url' => 'projects/' . $project->slug,
            'publishedAt' => $project->published_at?->format('d.m.Y'),
            'thumbnail' => public_asset($project->thumbnail),
            'categories' => $project->categories->map(static fn ($category) => [
                'name' => $category->name,
                'slug' => $category->slug,
                'url' => 'projects/category/' . $category->slug,
            ])->toArray(),
        ];
    }
}