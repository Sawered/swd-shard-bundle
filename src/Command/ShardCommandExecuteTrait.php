<?php


namespace Swd\Bundle\ShardBundle\Command;


use Doctrine\DBAL\Connection;
use Psr\Container\ContainerInterface;
use Swd\Bundle\ShardBundle\ConnectionRegistry;
use Swd\Bundle\ShardBundle\ShardIdsResolverInterface;
use Swd\Bundle\ShardBundle\ShardIdsSourceInterface;
use Symfony\Component\Console\Exception\InvalidArgumentException;
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

    /** @var ContainerInterface|null */
    protected $registryLocator = null;

    /** @var ContainerInterface|null */
    protected $connectionLocator = null;

    /**
     * @var ContainerInterface|null
     */
    protected $shardIdRegistryLocator = null;

    /**
     * @var bool
     */
    protected $shardOptionRequired = false;

    /**
     * @var null|int[]
     */
    protected $shardIds = null;

    /** @var null|int */
    protected $currentShardId = null;

    public function initialize(InputInterface $input, OutputInterface $output): void
    {
        $type = (string)$input->getArgument('type');

        if (is_null($this->registryLocator)) {
            throw new \LogicException(
                'registry locator is not configured'
            );
        }

        if ($this->registryLocator->has($type)) {
            $registry = $this->getConnectionRegistry($type);
            $shardIds = $this->getShardIdsFromInput($type, $input, $registry);

            if (empty($shardIds)) {
                throw new InvalidArgumentException('Shard option required, no shards found in registry');
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

    /**
     * @param ContainerInterface $connectionRegistryLocator
     */
    public function setRegistryLocator(ContainerInterface $connectionRegistryLocator): void
    {
        $this->registryLocator = $connectionRegistryLocator;
    }

    /**
     * @param ContainerInterface $shardIdResolversLocator
     */
    public function setShardResolverLocator(ContainerInterface $shardIdResolversLocator): void
    {
        $this->shardIdRegistryLocator = $shardIdResolversLocator;
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

        if ($this->currentShardId) {
            $registry = $this->getConnectionRegistry($type);
            return $registry->createConnection($this->currentShardId);
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

    protected function getMigrationConfiguration(
        InputInterface $input,
        OutputInterface $output
    ): Configuration {
        $type = $input->getArgument('type');
        $settings = $this->getMigrationTypeSettings($type);


        $conn = $this->getConnection($type);
        $config = MigrationHelper::makeConfiguration(
            $settings,
            $conn
        );

        $config->getOutputWriter()->setCallback(
            static function (string $message) use ($output): void {
                $output->writeln($message);
            }
        );

        return $config;
    }

    protected function getConnectionRegistry(string $type): ConnectionRegistry
    {
        $registry = $this->registryLocator->get($type);
        if (!$registry instanceof ConnectionRegistry) {
            throw new \LogicException('Invalid registry type. Registry must implement ' . ConnectionRegistry::class);
        }

        return $registry;
    }

    /**
     * @param string $connectionType
     * @param InputInterface $input
     * @param ConnectionRegistry $registry
     * @return int[]
     * @throws \InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws \LogicException
     */
    protected function getShardIdsFromInput(
        string $connectionType,
        InputInterface $input,
        ConnectionRegistry $registry
    ): array {
        $shardIds = (array)$input->getOption('shard');

        if ($resolver = $this->getShardIdResolver($connectionType)) {
            foreach ($shardIds as &$shardDefinition) {
                $shardDefinition = $resolver->resolveShardIds($shardDefinition);
            }
            $shardIds = array_reduce($shardIds, 'array_merge', []);
            $shardIds = array_unique($shardIds, SORT_NUMERIC);
        }

        if (empty($shardIds)) {
            if ($this->shardOptionRequired || $registry instanceof ShardIdsSourceInterface) {
                $shardIds = $registry->getShardIds();
            } else {
                throw new InvalidArgumentException('Shard option required');
            }
        }


        return $shardIds;
    }

    protected function getShardIdResolver(string $connectionType): ?ShardIdsResolverInterface
    {
        $resolver = null;
        if (is_null($this->shardIdRegistryLocator)) {
            throw new \LogicException(
                'shardIds resolver locator is not configured'
            );
        }

        if ($this->shardIdRegistryLocator->has($connectionType)) {
            $resolver = $this->shardIdRegistryLocator->get($connectionType);
        }
        return $resolver;
    }
}
