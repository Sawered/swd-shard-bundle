<?php
declare(strict_types=1);

namespace Swd\Bundle\ShardBundle;

interface ShardIdsResolverInterface
{

    /**
     * Resolve range or comma separated list of shards or names into array of ids
     * Must return non-empty array
     *
     * @param string $shardIdsDefinition
     * @return int[]
     * @throws \InvalidArgumentException if result list is empty or cannot be resolved
     */
    public function resolveShardIds(string $shardIdsDefinition): array;
}
