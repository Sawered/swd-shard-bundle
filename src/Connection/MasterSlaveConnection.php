<?php

namespace Swd\Bundle\ShardBundle\Connection;
use Doctrine\DBAL\Connections\MasterSlaveConnection as BaseConnection;

class MasterSlaveConnection extends BaseConnection
{
    private $_isConnected = false;

    /**
     * {@inheritDoc}
     */
    public function connect($connectionName = null)
    {
        $result = parent::connect($connectionName);
        $this->_isConnected = true;

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function isConnected()
    {
        return $this->_isConnected;
    }

    /**
     * {@inheritDoc}
     */
    public function close()
    {

        $this->connections['master'] = null;
        $this->connections['slave'] = null;
        $this->_conn = null;

        $this->_isConnected = false;

    }
}
