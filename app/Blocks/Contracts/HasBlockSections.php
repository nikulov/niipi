<?php

namespace App\Blocks\Contracts;

interface HasBlockSections
{
    /** Return blocks for section or for all (if null) */
    public function getBlocksForSection(?string $section): array;
    
    /** Used for cache key */
    public function getRenderCacheId(): string;
    
    /** Used for cache key */
    public function getRenderUpdatedAtTimestamp(): int;
}