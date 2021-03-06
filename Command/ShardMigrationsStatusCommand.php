<?php
namespace Swd\Bundle\ShardBundle\Command;
use Doctrine\Migrations\Tools\Console\Command\StatusCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ShardMigrationsStatusCommand  extends StatusCommand
{
    use ShardCommandExecuteTrait;
    
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('shard:migrations:status')
            ->addArgument('type',InputArgument::REQUIRED,'set type of configured migration')
            ->addOption('shard', null, InputOption::VALUE_REQUIRED, 'The shard connection to use for this command.')
        ;
    }

}

