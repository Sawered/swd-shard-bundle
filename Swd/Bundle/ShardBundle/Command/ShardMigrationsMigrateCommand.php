<?php
namespace Swd\Bundle\ShardBundle\Command;
use Doctrine\DBAL\Migrations\Tools\Console\Command\MigrateCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ShardMigrationsMigrateCommand extends MigrateCommand
{
    use ShardCommandExecuteTrait;

    protected function configure()
    {
        $this->addArgument('type',InputArgument::REQUIRED,'set type of configured migration')
        ;
        parent::configure();

        $this
            ->setName('shard:migrations:migrate')
            ->addOption('shard', null, InputOption::VALUE_REQUIRED, 'The shard connection to use for this command.')
        ;
    }

}

