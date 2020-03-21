<?php

namespace Swd\Bundle\ShardBundle\Command;

use Doctrine\DBAL\Migrations\Tools\Console\Command\ExecuteCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ShardMigrationsExecuteCommand extends ExecuteCommand
{

    use ShardCommandExecuteTrait;

    protected static $defaultName = 'shard:migrations:execute';

    public function __construct()
    {
        parent::__construct(self::$defaultName);
    }

    protected function configure()
    {
        $this
            ->addArgument('type', InputArgument::REQUIRED, 'set type of configured migration');
        parent::configure();

        $this
            ->addOption('shard', null, InputOption::VALUE_REQUIRED, 'The shard connection to use for this command.')
        ;
    }



}

