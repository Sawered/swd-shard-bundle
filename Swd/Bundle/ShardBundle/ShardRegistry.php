<?php

namespace Swd\Bundle\ShardBundle;


/**
 *
 * @author skoryukin
 **/
class ShardRegistry
{
    private $options;

    protected function setShardOptions($shardId,$options)
    {
        $this->options[$shardId] = $options;
    }

    public function getShardOptions($shardId)
    {
        if(isset($this->options[$shardId])){
            return $this->options[$shardId];
        }
        return null;
    }

    /**
     * Возвращает список шард, для которых есть настройки
     *
     * @return array
     * @author skoryukin
     **/
    public function getShardsList()
    {
        return array_keys($this->options);
    }
}
