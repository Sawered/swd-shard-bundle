<?php
declare(strict_types=1);

namespace Swd\Bundle\ShardBundle\Command;

use Doctrine\DBAL\Connection;
use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\OutputWriter;
use Swd\Bundle\ShardBundle\ConnectionRegistry;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

final class MigrationHelper
{

    public static function makeConfiguration(array $settings, Connection $conn): Configuration
    {
        $conf = new Configuration($conn);
        $conf->setMigrationsTableName($settings['table_name']);
        $conf->setMigrationsNamespace($settings['namespace']);

        $conf->setMigrationsDirectory($settings['directory']);

        return $conf;
    }

}
