<?php

namespace Swd\Bundle\ShardBundle\Connection;
#use Doctrine\DBAL\Connections\MasterSlaveConnection;
use Closure;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;

/**
 * Основное отличие от MasterSlaveConnection это позднее создание
 * самого коннекта к базе и возможность его поменять при переключении между шардами
 * прозрачно для сервисов, которые завязаны на коннект
 *
 * @package default
 * @author skoryukin
**/
class ShardConnection extends MasterSlaveConnection
{
    /** @var  MasterSlaveConnection */
    private $connection;
    private $selectedShard;

    protected $commonParams;
    protected $commonDriver;
    protected $commonConfig;
    protected $commonEventManager;

    /** @noinspection PhpMissingParentConstructorInspection */


    /**
     * Конструктор, ленивый
     *
     * @params - ignoring
     * @param array $params
     * @param Driver $driver
     * @param Configuration|null $config
     * @param EventManager|null $eventManager
     * @author skoryukin
     */
    public function __construct(array $params, Driver $driver, Configuration $config = null, EventManager $eventManager = null)
    {
        $this->commonParams = $params;
        $this->commonDriver = $driver;
        $this->commonConfig = $config;
        $this->commonEventManager = $eventManager;
    }

    /**
     * Подключение к шарде
     *
     * @param int $shard  имя или id шарды
     * @param array $params array параметры соединения
     * @param string $connName тип соединения master|slave
     *
     * @return bool
     * @author skoryukin
     **/
    public function connectToShard($shard, array $params, $connName = null)
    {
        if($this->selectedShard == $shard  && $this->connection){
            return $this->connection->connect($connName);
        }

        $this->setupConnection($shard,$params);

        return $this->connection->connect($connName);
    }

    /**
     * Настройка коннекта в lazy режиме
     *
     * @param $shard
     * @param array $params
     * @return void
     * @author skoryukin
     */
    public function setupConnection($shard,array $params)
    {
        if($this->selectedShard == $shard  && $this->connection){
            return;
        }

        $connection = $this->createConnection($params);
        $this->switchConnection($connection,$shard);
    }

    protected function createConnection($params)
    {
        $connection = new MasterSlaveConnection(
                            $params,
                            $this->commonDriver,
                            $this->commonConfig,
                            $this->commonEventManager
                        );
        return $connection;
    }

    protected function switchConnection(Connection $connection,$shard)
    {
        if($this->connection){
            $this->connection->close();
        }

        $this->connection =  $connection;
        $this->selectedShard = $shard;
    }

    protected function ensureShardSelected()
    {

        if($this->connection){
            return;
        }

        throw new \Exception('Shard not selected');
    }

    /**
     * {@inheritDoc}
     */
    public function connect($connectionName = null)
    {
        $this->ensureShardSelected();

        return $this->connection->connect($connectionName);
    }

    /**
     * {@inheritDoc}
     */
    public function isConnectedToMaster()
    {
        $this->ensureShardSelected();

        return $this->connection->isConnectedToMaster();
    }

    /**
     * {@inheritDoc}
     */
    public function getParams()
    {
        $this->ensureShardSelected();

        return $this->connection->getParams();
    }

    /**
     * {@inheritDoc}
     */
    public function getDatabase()
    {
        $this->ensureShardSelected();

        return $this->connection->getDatabase();
    }

    /**
     * {@inheritDoc}
     */
    public function getHost()
    {
        $this->ensureShardSelected();

        return $this->connection->getHost();
    }

    /**
     * {@inheritDoc}
     */
    public function getPort()
    {
        $this->ensureShardSelected();

        return $this->connection->getPort();
    }

    /**
     * {@inheritDoc}
     */
    public function getUsername()
    {
        $this->ensureShardSelected();

        return $this->connection->getUsername();
    }

    /**
     * {@inheritDoc}
     */
    public function getPassword()
    {
        $this->ensureShardSelected();

        return $this->connection->getPassword();
    }

    /**
     * {@inheritDoc}
     */
    public function getDriver()
    {
        $this->ensureShardSelected();

        return $this->connection->getDriver();
    }

    /**
     * {@inheritDoc}
     */
    public function getConfiguration()
    {
        $this->ensureShardSelected();

        return $this->connection->getConfiguration();
    }

    /**
     * {@inheritDoc}
     */
    public function getEventManager()
    {
        $this->ensureShardSelected();

        return $this->connection->getEventManager();
    }

    /**
     * {@inheritDoc}
     */
    public function getDatabasePlatform()
    {
        $this->ensureShardSelected();

        return $this->connection->getDatabasePlatform();
    }

    /**
     * {@inheritDoc}
     */
    public function getExpressionBuilder()
    {
        $this->ensureShardSelected();

        return $this->connection->getExpressionBuilder();
    }

    /**
     * {@inheritDoc}
     */
    public function executeCacheQuery($query, $params, $types, QueryCacheProfile $qcp)
    {
        $this->ensureShardSelected();

        return $this->connection->executeCacheQuery($query, $params, $types, $qcp);
    }

    /**
     * {@inheritDoc}
     */
    public function project($query, array $params, Closure $function)
    {
        $this->ensureShardSelected();

        return $this->connection->project($query,$params,$function);
    }

    /**
     * {@inheritDoc}
     */
    public function errorCode()
    {
        $this->ensureShardSelected();
        return $this->connection->errorCode();
    }

    /**
     * {@inheritDoc}
     */
    public function errorInfo()
    {
        $this->ensureShardSelected();
        return $this->connection->errorInfo();
    }

    /**
     * {@inheritDoc}
     */
    public function lastInsertId($seqName = null)
    {

        $this->ensureShardSelected();

        return $this->connection->lastInsertId($seqName);
    }

    /**
     * {@inheritDoc}
     */
    public function transactional(Closure $func)
    {
        $this->ensureShardSelected();

        $this->connection->transactional($func);
    }

    /**
     * {@inheritDoc}
     */
    public function setNestTransactionsWithSavepoints($nestTransactionsWithSavepoints)
    {
        $this->ensureShardSelected();

        $this->connection->setNestTransactionsWithSavepoints($nestTransactionsWithSavepoints);
    }

    /**
     * {@inheritDoc}
     */
    public function getNestTransactionsWithSavepoints()
    {
        $this->ensureShardSelected();

        return $this->connection->getNestTransactionsWithSavepoints();
    }


    /**
     * {@inheritDoc}
     */
    public function getWrappedConnection()
    {
        $this->ensureShardSelected();
        return $this->connection->getWrappedConnection();
    }


    /**
     * {@inheritDoc}
     */
    public function getSchemaManager()
    {
        $this->ensureShardSelected();
        return $this->connection->getSchemaManager();
    }

    /**
     * {@inheritDoc}
     */
    public function setRollbackOnly()
    {
        $this->ensureShardSelected();
        $this->connection->setRollbackOnly();
    }

    /**
     * {@inheritDoc}
     */
    public function isRollbackOnly()
    {
        $this->ensureShardSelected();
        return $this->connection->isRollbackOnly();
    }

    /**
     * {@inheritDoc}
     */
    public function setFetchMode($fetchMode)
    {
        $this->ensureShardSelected();
        $this->connection->setFetchMode($fetchMode);
    }

    /**
     * {@inheritDoc}
     */
    public function fetchAssoc($statement, array $params = array(), array $types = array())
    {
        $this->ensureShardSelected();
        return $this->connection->fetchAssoc($statement, $params, $types);
    }

    /**
     * {@inheritDoc}
     */
    public function fetchArray($statement, array $params = array(), array $types = array())
    {
        $this->ensureShardSelected();
        return $this->connection->fetchArray($statement, $params, $types);
    }

    /**
     * {@inheritDoc}
     */
    public function fetchColumn($statement, array $params = array(), $colnum = 0, array $types = array())
    {
        $this->ensureShardSelected();
        return $this->connection->fetchColumn($statement, $params, $colnum, $types);
    }

    /**
     * {@inheritDoc}
     */
    public function isConnected()
    {
        $this->ensureShardSelected();
        return $this->connection->isConnected();
    }

    /**
     * {@inheritDoc}
     */
    public function isTransactionActive()
    {
        $this->ensureShardSelected();
        return $this->connection->isTransactionActive();
    }

    /**
     * {@inheritDoc}
     */
    public function quoteIdentifier($str)
    {
        $this->ensureShardSelected();
        return $this->connection->quoteIdentifier($str);
    }

    /**
     * {@inheritDoc}
     */
    public function quote($input, $type = null)
    {
        $this->ensureShardSelected();
        return $this->connection->quote($input,$type);
    }

    /**
     * {@inheritDoc}
     */
    public function fetchAll($sql, array $params = array(), $types = array())
    {
        $this->ensureShardSelected();
        return $this->connection->fetchAll($sql, $params, $types);
    }

    /**
     * {@inheritDoc}
     */
    public function executeQuery($query, array $params = array(), $types = array(), QueryCacheProfile $qcp = null)
    {
        $this->ensureShardSelected();

        return $this->connection->executeQuery($query,$params,$types,$qcp);
    }



    /**
     * {@inheritDoc}
     */
    public function executeUpdate($query, array $params = array(), array $types = array())
    {

        $this->ensureShardSelected();

        return $this->connection->executeUpdate($query, $params, $types);
    }

    /**
     * {@inheritDoc}
     */
    public function beginTransaction()
    {
        $this->ensureShardSelected();

        $this->connection->beginTransaction();
    }

    /**
     * {@inheritDoc}
     */
    public function commit()
    {
        $this->ensureShardSelected();

        $this->connection->commit();
    }

    /**
     * {@inheritDoc}
     */
    public function rollBack()
    {
        $this->ensureShardSelected();

        $this->connection->rollBack();
    }

    /**
     * {@inheritDoc}
     */
    public function delete($tableName, array $identifier, array $types = array())
    {
        $this->ensureShardSelected();

        return $this->connection->delete($tableName, $identifier, $types);
    }

    /**
     * {@inheritDoc}
     */
    public function close()
    {
        //$this->ensureShardSelected();
        if($this->connection){
            //Проверка наличия приводит к Too many connections
            //if($this->connection->isConnected()){
                $this->connection->close();
            //}

            $this->connection = null;
            $this->selectedShard = null;
        }

    }

    public function setTransactionIsolation($level)
    {
        $this->ensureShardSelected();

        return $this->connection->setTransactionIsolation($level);
    }

    public function getTransactionIsolation()
    {
        $this->ensureShardSelected();

        return $this->connection->getTransactionIsolation();
    }

    /**
     * {@inheritDoc}
     */
    public function update($tableName, array $data, array $identifier, array $types = array())
    {
        $this->ensureShardSelected();

        return $this->connection->update($tableName, $data, $identifier, $types);
    }

    /**
     * {@inheritDoc}
     */
    public function insert($tableName, array $data, array $types = array())
    {
        $this->ensureShardSelected();

        return $this->connection->insert($tableName, $data, $types);
    }

    /**
     * {@inheritDoc}
     */
    public function exec($statement)
    {
        $this->ensureShardSelected();

        return $this->connection->exec($statement);
    }

    public function getTransactionNestingLevel()
    {
        $this->ensureShardSelected();

        return $this->connection->getTransactionNestingLevel();
    }

    /**
     * {@inheritDoc}
     */
    public function createSavepoint($savepoint)
    {
        $this->ensureShardSelected();

        $this->connection->createSavepoint($savepoint);
    }

    /**
     * {@inheritDoc}
     */
    public function releaseSavepoint($savepoint)
    {
        $this->ensureShardSelected();

        $this->connection->releaseSavepoint($savepoint);
    }

    /**
     * {@inheritDoc}
     */
    public function rollbackSavepoint($savepoint)
    {
        $this->ensureShardSelected();

        $this->connection->rollbackSavepoint($savepoint);
    }

    /**
     * {@inheritDoc}
     */
    public function query()
    {
        $this->ensureShardSelected();

        $args = func_get_args();
        $statement = call_user_func_array(array($this->connection, 'query'), $args);
        return $statement;

    }

    /**
     * {@inheritDoc}
     */
    public function prepare($statement)
    {
        $this->ensureShardSelected();

        return $this->connection->prepare($statement);
    }

    public function convertToDatabaseValue($value, $type)
    {
        $this->ensureShardSelected();

        return $this->connection->convertToDatabaseValue($value, $type);
    }

    public function convertToPHPValue($value, $type)
    {
        $this->ensureShardSelected();

        return $this->connection->convertToPHPValue($value, $type);
    }

    public function resolveParams(array $params, array $types)
    {
        $this->ensureShardSelected();

        return $this->connection->resolveParams($params, $types);
    }

    public function createQueryBuilder()
    {
        $this->ensureShardSelected();

        return $this->connection->createQueryBuilder();
    }
}
