<?php


namespace Swd\Bundle\ShardBundle\Command;


use Doctrine\DBAL\Connection;
use Psr\Container\ContainerInterface;
use Swd\Bundle\ShardBundle\ConnectionRegistry;
use Swd\Bundle\ShardBundle\ShardIdsSourceInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \RuntimeException
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
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

        $this->setMigrationConfiguration($config);
        parent::execute($input, $output);
    }
}
