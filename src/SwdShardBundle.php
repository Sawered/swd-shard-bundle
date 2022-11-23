<?php

namespace Swd\Bundle\ShardBundle;

use Swd\Bundle\ShardBundle\DependencyInjection\LocatorCompilerPass;
use Symfony\Component\Console\DependencyInjection\AddConsoleCommandPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SwdShardBundle extends Bundle
{
    /**
     * @inheritDoc
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
//        $container->addCompilerPass(new AddConsoleCommandPass(), PassConfig::TYPE_BEFORE_REMOVING);

        $container->addCompilerPass(new LocatorCompilerPass());
    }

}
