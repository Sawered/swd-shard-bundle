<?php

namespace Swd\Bundle\ShardBundle;

use Swd\Bundle\ShardBundle\DependencyInjection\LocatorCompilerPass;
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

        $container->addCompilerPass(new LocatorCompilerPass());
    }

}
