<?php

namespace Swd\Bundle\ShardBundle\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\OutputWriter;
use Swd\Bundle\ShardBundle\ConnectionRegistry;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MigrationHelper
{


    public static function makeConfiguration($name, array $settings, Connection $conn, OutputInterface $output = null)
    {
        $conf = new Configuration($conn, static::getOutputWriter($output));
        $conf->setMigrationsTableName($settings['table_name']);
        $conf->setMigrationsNamespace($settings['namespace']);

        $conf->setMigrationsDirectory($settings['directory']);

        //$conf->registerMigrationsFromDirectory($settings['directory']);
        return $conf;
    }


    public static function getOutputWriter(OutputInterface $output)
    {
        return new OutputWriter(function ($message) use ($output) {
            return $output->writeln($message);
        });
    }
}
