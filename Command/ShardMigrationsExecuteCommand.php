<?php
namespace Swd\Bundle\ShardBundle\Command;
use Doctrine\DBAL\Migrations\Tools\Console\Command\ExecuteCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ShardMigrationsExecuteCommand extends ExecuteCommand
{

    use ShardCommandExecuteTrait;
    protected function configure()
    {
        $this
            ->addArgument('type',InputArgument::REQUIRED,'set type of configured migration')
            ;
        parent::configure();

        $this
            ->setName('shard:migrations:execute')
            ->addOption('shard', null, InputOption::VALUE_REQUIRED, 'The shard connection to use for this command.')
        ;
    }


}

