<?php

namespace Nucleus\Bundle\MigrationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree.
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('nucleus_migration');

        $rootNode
            ->children()
                ->arrayNode('versions')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('tasks')
                    ->prototype('array')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('command')->cannotBeEmpty()->end()
                                ->variableNode('parameters')
                                    ->validate()
                                    ->always(function ($v) {
                                        if (!is_array($v)) {
                                            throw new InvalidTypeException();
                                        }
                                        return $v;
                                    })
                                    ->end()
                                ->end()
                                ->scalarNode('salt')->defaultNull()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
