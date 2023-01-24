<?php

namespace Swd\Bundle\ShardBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('swd_shard');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        $rootNode->children()
            ->scalarNode('registry')->end()
        ->end();

        $this->addMigrationsSection($rootNode);

        return $treeBuilder;
    }

    /**
     * @param ArrayNodeDefinition $rootNode
     */
    public function addMigrationsSection($rootNode)
    {
        /** @var ArrayNodeDefinition $migrationNode */
        $migrationNode = $rootNode->children()
            ->arrayNode('migrations')
            ->useAttributeAsKey('name')
            ->prototype('array')
        ;


        $migrationNode->children()
            ->scalarNode('table_name')->defaultValue('doctrine_migration_versions')->end()
            ->scalarNode('namespace')->defaultValue('Application\\Migrations')->end()
            ->scalarNode('directory')->defaultValue('%kernel.root_dir%/DoctrineMigrations')->end()
            ->scalarNode('connection_registry')->defaultNull()->end()
            ->scalarNode('shard_resolver')->defaultNull()->end()
        ->end();


    }
}
