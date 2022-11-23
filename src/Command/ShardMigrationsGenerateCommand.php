<?php
namespace Swd\Bundle\ShardBundle\Command;
use Doctrine\Migrations\Tools\Console\Command\GenerateCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ShardMigrationsGenerateCommand extends GenerateCommand
{
    use ShardCommandExecuteTrait;

    protected static $defaultName ='shard:migrations:generate';

    public function __construct()
    {
        parent::__construct(null);
        $this->shardOptionRequired = true;
    }

    protected function configure(): void
    {
        parent::configure();

        $this
            ->addArgument('type',InputArgument::REQUIRED,'set type of configured migration')
            ->addOption('shard', null, InputOption::VALUE_REQUIRED, 'The shard connection to use for this command.')
        ;
    }
}
