<?php

namespace Swd\Bundle\ShardBundle\Command;

use Doctrine\Migrations\Tools\Console\Command\StatusCommand;
use Swd\Bundle\ShardBundle\ConnectionRegistry;
use Swd\Bundle\ShardBundle\ShardIdsSourceInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ShardMigrationsStatusCommand extends StatusCommand
{
    use ShardCommandExecuteTrait;

    protected static $defaultName = 'shard:migrations:status';
    protected $shardIds = null;

    public function __construct()
    {
        parent::__construct(null);
    }

    protected function configure(): void
    {
        parent::configure();

        $this
            ->addArgument('type', InputArgument::REQUIRED, 'set type of configured migration')
            ->addOption('shard', null, InputOption::VALUE_REQUIRED, 'The shard connection to use for this command.');
    }



}

