<?php

namespace App\Presenters\Blocks;


use App\Models\Project;

final class ProjectsBlockPresenter
{
    public static function make(Project $project): array
    {
        return [
            'title' => $project->title,
            'description' => $project->description,
            'url' => url('projects/' . $project->slug),
            'thumbnail' => public_asset($project->thumbnail),
            'publishedAt' => $project->published_at?->format('d.m.Y'),
            'categories' => $project->categories->map(static fn ($category) => [
                'name' => $category->name,
                'slug' => $category->slug,
                'url' => url('projects/category/' . $category->slug),
            ])->toArray(),
        ];
    }
}