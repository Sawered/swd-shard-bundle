<?php


namespace Swd\Bundle\ShardBundle\DependencyInjection;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class LocatorCompilerPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        $conf = $container->getParameter('swd_shard.migrations');
        $services = [];
        $connectionServices = [];
        foreach ($conf as $type => $typeConf) {
            if (isset($typeConf['connection_registry'])) {
                $services[$type] = new Reference($typeConf['connection_registry']);
            } else {
                $connectionServices[$type] = new Reference(sprintf('doctrine.dbal.%s_connection', $type));
            }
        }

        $commands = $container->findTaggedServiceIds('swd_shard.migration_command');
        foreach ($commands as $serviceId => $tags) {
            $definition = $container->getDefinition($serviceId);
            $definition->addMethodCall('setMigrationsConfig', [$conf]);
            $definition->addMethodCall('setRegistryLocator',
                [ServiceLocatorTagPass::register($container, $services)]);
            $definition->addMethodCall('setConnectionLocator',
                [ServiceLocatorTagPass::register($container, $connectionServices)]);
        }
    }

}
