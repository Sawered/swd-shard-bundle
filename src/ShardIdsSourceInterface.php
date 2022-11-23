<?php

namespace Swd\Bundle\ShardBundle;

interface ShardIdsSourceInterface
{
    public function getShardIds(): array;
}
