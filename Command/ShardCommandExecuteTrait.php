<?php


namespace Swd\Bundle\ShardBundle\Command;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Trait ShardCommandExecuteTrait
 * @package Swd\Bundle\ShardBundle\Command
 */
trait ShardCommandExecuteTrait
{

    public function execute(InputInterface $input, OutputInterface $output)
    {
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

        $this->setMigrationConfiguration($config);
        parent::execute($input, $output);
    }

}
