<?php
namespace Swd\Bundle\ShardBundle\Command;
use Doctrine\DBAL\Migrations\Tools\Console\Command\StatusCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ShardMigrationsStatusCommand  extends StatusCommand
{
    use ShardCommandExecuteTrait;
    protected static $defaultName = 'shard:migrations:status';

    public function __construct()
    {
        parent::__construct(null);
    }

    protected function configure()
    {
        parent::configure();

        $this

            ->addArgument('type',InputArgument::REQUIRED,'set type of configured migration')
            ->addOption('shard', null, InputOption::VALUE_REQUIRED, 'The shard connection to use for this command.')
        ;
    }

}

