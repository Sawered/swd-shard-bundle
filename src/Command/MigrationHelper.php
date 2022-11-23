<?php

namespace Swd\Bundle\ShardBundle\Command;

use Doctrine\DBAL\Connection;
use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\OutputWriter;
use Swd\Bundle\ShardBundle\ConnectionRegistry;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class MigrationHelper
{

    public static function getMigrationTypeSettings(string $type, ContainerInterface $container)
    {
        $migrations = $container->getParameter('swd_shard.migrations');

        if (!array_key_exists($type, $migrations)) {
            throw new \Exception('Configuration missing for type: ' . $type);
        }

        return $migrations[$type];
    }

    public static function makeConfiguration(string $name, array $settings, Connection $conn, OutputInterface $output = null)
    {
        $conf = new Configuration($conn);
        $conf->setMigrationsTableName($settings['table_name']);
        $conf->setMigrationsNamespace($settings['namespace']);

        $conf->setMigrationsDirectory($settings['directory']);

        //$conf->registerMigrationsFromDirectory($settings['directory']);
        return $conf;
    }

    /**
     * @param string $type
     * @param ContainerInterface $container
     * @param null $connName
     * @return Connection
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     */
    public static function getConnection(string $type, ContainerInterface $container, $connName = null)
    {
        $sett = static::getMigrationTypeSettings($type, $container);
        if (is_null($connName)) {
            $connName = $type;
        }

        if (!empty($sett['connection_registry'])) {
            //use registry, no other defaults
            if (!$container->has($sett['connection_registry'])) {
                throw new \Exception('Unexisting connection registry: ' . $sett['connection_registry']);
            }
            /** @var ConnectionRegistry $registry */
            $registry = $container->get($sett['connection_registry']);
            return $registry->createConnection($connName);
        }


        if (!empty($sett['connection'])) {
            $connName = $sett['connection'];
        }

        $conn_service = sprintf('doctrine.dbal.%s_connection', $type);

        if ($container->has($conn_service)) {
            $connection = $container->get($conn_service);
            if (!$connection instanceof Connection) {
                throw new \Exception(sprintf('DBAL\Connection expected, "%s" given ', get_class($connection)));
            }
            return $connection;
        }

        throw new \Exception(sprintf('Cannot find connection for migration type: %s', $type));
    }

    public static function getOutputWriter(OutputInterface $output)
    {
        return new OutputWriter(function ($message) use ($output) {
            return $output->writeln($message);
        });
    }
}
