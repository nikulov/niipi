<?php

namespace App\Models\Concerns;

use App\Services\ModelDuplicator;
use Illuminate\Database\Eloquent\Model;

trait Duplicatable
{
    public function duplicate(): static
    {
        return app(ModelDuplicator::class)->duplicate($this);
    }

    public function duplicateTitleColumn(): string
    {
        return 'title';
    }

    public function duplicateSlugColumn(): ?string
    {
        return 'slug';
    }

    abstract public function prepareDuplicate(Model $copy): void;

    public function copyRelationsTo(Model $copy): void
    {
        // no-op; override per model
    }
}
