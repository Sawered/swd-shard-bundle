<?php


namespace Swd\Bundle\ShardBundle\Command;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Migrations\Configuration\Configuration;

/**
 * Trait ShardCommandExecuteTrait
 * @package Swd\Bundle\ShardBundle\Command
 */
trait ShardCommandExecuteTrait
{

    protected function getMigrationConfiguration(
        InputInterface $input,
        OutputInterface $output
    ) : Configuration {

        $container = $this->getApplication()->getKernel()->getContainer();
        $type = $input->getArgument('type');
        $settings = MigrationHelper::getMigrationTypeSettings($type,$container);

        $connName = $input->getOption('shard');
        $conn = MigrationHelper::getConnection($type,$container,$connName);
        $config = MigrationHelper::makeConfiguration(
            $type,
            $settings,
            $conn,
            $output
        );
        
        return $config;
    }
}
