<?php


namespace Swd\Bundle\ShardBundle\DependencyInjection;


use Symfony\Component\Config\Definition\Exception\Exception;
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
        $extConf = $container->getExtensionConfig('swd_shard');
        if(empty($extConf)){
            throw new Exception('Empty bundle configuration for '.get_class($this));
        }

        $conf = reset($extConf)['migrations']??[];

        $registries = [];
        $resolvers = [];
        $connectionServices = [];
        foreach ($conf as $type => $typeConf) {
            if (isset($typeConf['connection_registry'])) {
                $registries[$type] = new Reference($typeConf['connection_registry']);
            } else {
                $connectionServices[$type] = new Reference(sprintf('doctrine.dbal.%s_connection', $type));
            }

            if(!empty($typeConf['shard_resolver'])){
                $resolvers[$type] = new Reference($typeConf['shard_resolver']);
            }
        }

        $commands = $container->findTaggedServiceIds('swd_shard.migration_command');
        foreach ($commands as $serviceId => $tags) {
            $definition = $container->getDefinition($serviceId);
            $definition->addMethodCall('setMigrationsConfig', [$conf]);
            $definition->addMethodCall('setRegistryLocator',
                [ServiceLocatorTagPass::register($container, $registries)]);

            $definition->addMethodCall('setShardResolverLocator',
                [ServiceLocatorTagPass::register($container, $resolvers)]);

            $definition->addMethodCall('setConnectionLocator',
                [ServiceLocatorTagPass::register($container, $connectionServices)]);
        }
    }

}
