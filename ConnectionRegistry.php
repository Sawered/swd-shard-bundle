<?php

namespace Swd\Bundle\ShardBundle;

interface ConnectionRegistry
{
    public function createConnection($shardId);
}
