<?php

namespace Tests\Support;

use App\Blocks\Contracts\HasBlockSections;

class StubHasBlockSections implements HasBlockSections
{
    public function __construct(
        private readonly array $blocks = [],
        private readonly string $cacheId = 'stub:1',
        private readonly int $timestamp = 0,
    ) {}

    public function getBlocksForSection(?string $section): array
    {
        return $this->blocks;
    }

    public function getRenderCacheId(): string
    {
        return $this->cacheId;
    }

    public function getRenderUpdatedAtTimestamp(): int
    {
        return $this->timestamp;
    }
}
