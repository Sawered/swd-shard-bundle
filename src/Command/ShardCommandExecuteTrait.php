<?php


namespace Swd\Bundle\ShardBundle\Command;


use Doctrine\DBAL\Connection;
use Psr\Container\ContainerInterface;
use Swd\Bundle\ShardBundle\ConnectionRegistry;
use Swd\Bundle\ShardBundle\ShardIdsSourceInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Migrations\Configuration\Configuration;

/**
 * Trait ShardCommandExecuteTrait
 * @package Swd\Bundle\ShardBundle\Command
 */
trait ShardCommandExecuteTrait
{
    protected $migrationsConfig = [];

    /** @var ContainerInterface */
    protected $registryLocator;

    /** @var ContainerInterface */
    protected $connectionLocator;

    /**
     * @var bool
     */
    protected $shardOptionRequired = false;

    /**
     * @var null|int[]
     */
    protected $shardIds = null;

    /** @var null|int  */
    protected $currentShardId = null;

    public function initialize(InputInterface $input, OutputInterface $output): void
    {
        $type = $input->getArgument('type');

        if ($this->registryLocator->has($type)) {
            $registry = $this->registryLocator->get($type);
            if (!$registry instanceof ConnectionRegistry) {
                throw new \LogicException(
                    'Invalid registry type. Registry must implement ' . ConnectionRegistry::class
                );
            }

            $shardIds = (array)$input->getOption('shard');
            if (empty($shardIds)) {
                if ($this->shardOptionRequired || $registry instanceof ShardIdsSourceInterface) {
                    $shardIds = $registry->getShardIds();
                } else {
                    throw new \InvalidArgumentException('Shard option required');
                }
            }
            if(empty($shardIds)){
                throw new \InvalidArgumentException('Shard option required, no shards found in registry');
            }
            $this->shardIds = $shardIds;
        }
    }

    public function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $res = 0;
        if (is_null($this->shardIds)) {
            parent::initialize($input, $output);
            return parent::execute($input, $output);
        } else {
            foreach ($this->shardIds as $shardId) {
                $this->currentShardId = $shardId;
                $output->writeln(sprintf("\nProcessing shard %s", $shardId));

                parent::initialize($input, $output);
                $res |= parent::execute($input, $output);
            }
        }

        return $res;
    }

    protected function getMigrationConfigurationOld(
        InputInterface $input,
        OutputInterface $output
    ) : Configuration {

        $container = $this->getApplication()->getKernel()->getContainer();
        $type = $input->getArgument('type');
        $settings = $this->getMigrationTypeSettings($type);

        $connections = null;
        if ($this->registryLocator->has($type)) {

            $registry = $this->registryLocator->get($type);
            if (!$registry instanceof ConnectionRegistry) {
                throw new \LogicException('Invalid registry type. Registry must implement ' . ConnectionRegistry::class);
            }

            $shardIds = (array)$input->getOption('shard');
            if (empty($shardIds)) {
                if ($this->shardOptionRequired || $registry instanceof ShardIdsSourceInterface) {
                    $shardIds = $registry->getShardIds();
                } else {
                    throw new \InvalidArgumentException('Shard option required');
                }
            }

            foreach ($shardIds as $shardId) {
                $output->writeln(sprintf("\nProcessing shard %s", $shardId));
                $conn = $registry->createConnection($shardId);
                $this->processConnection($conn, $input, $output, $type, $settings);
            }

        } else {

            $conn = $this->getConnection($type);

            $this->processConnection($conn, $input, $output, $type, $settings);
        }
    }

    protected function getMigrationConfiguration(
        InputInterface $input,
        OutputInterface $output
    ) : Configuration {

        $container = $this->getApplication()->getKernel()->getContainer();
        $type = $input->getArgument('type');
        $settings = MigrationHelper::getMigrationTypeSettings($type,$container);

        $connName = $this->currentShardId;
        $conn = MigrationHelper::getConnection($type,$container,$connName);
        $config = MigrationHelper::makeConfiguration(
            $type,
            $settings,
            $conn,
            $output
        );

        $config->getOutputWriter()->setCallback(
            static function (string $message) use ($output) : void {
                $output->writeln($message);
            }
        );

        return $config;
    }
    /**
     * @param ContainerInterface $connectionRegistryLocator
     */
    public function setRegistryLocator(ContainerInterface $connectionRegistryLocator)
    {
        $this->registryLocator = $connectionRegistryLocator;
    }

    /**
     * @param array $migrationsConfig
     */
    public function setMigrationsConfig(array $migrationsConfig)
    {
        $this->migrationsConfig = $migrationsConfig;
    }

    /**
     * @param string $type
     * @return array
     * @throws \RuntimeException
     */
    public function getMigrationTypeSettings(string $type)
    {
        if (!array_key_exists($type, $this->migrationsConfig)) {
            throw new \RuntimeException('Configuration missing for type: ' . $type);
        }

        return $this->migrationsConfig[$type];
    }


    /**
     * @param string $type
     * @return Connection
     * @throws \RuntimeException
     */
    public function getConnection(string $type)
    {
        if ($this->connectionLocator->has($type)) {
            $connection = $this->connectionLocator->get($type);
            if (!$connection instanceof Connection) {
                throw new \RuntimeException(sprintf('DBAL\Connection expected, "%s" given ', get_class($connection)));
            }

            return $connection;
        }

        throw new \RuntimeException(sprintf('Cannot find connection for migration type: %s', $type));
    }

    /**
     * @param ContainerInterface $connectionLocator
     */
    public function setConnectionLocator(ContainerInterface $connectionLocator)
    {
        $this->connectionLocator = $connectionLocator;
    }

    /**
     * @param $conn
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param $type
     * @param array $settings
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    protected function processConnection(
        Connection $conn,
        InputInterface $input,
        OutputInterface $output,
        string $type,
        array $settings
    ) {
        $config = MigrationHelper::makeConfiguration(
            $type,
            $settings,
            $conn,
            $output
        );

        $config->getOutputWriter()->setCallback(
            static function (string $message) use ($output) : void {
                $output->writeln($message);
            }
        );

        return $config;
    }
}
