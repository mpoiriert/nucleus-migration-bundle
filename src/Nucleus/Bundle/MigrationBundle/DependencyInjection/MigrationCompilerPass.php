<?php

namespace Nucleus\Bundle\MigrationBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class MigrationCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('nucleus.migration.coordinator')) {
            return;
        }

        $versions = $container->getParameter('nucleus_migration.versions');
        $tasks = $container->getParameter('nucleus_migration.tasks');

        $definition = $container->getDefinition('nucleus.migration.coordinator');

        $definition->addMethodCall(
            'setVersions',
            array($versions)
        );

        foreach($tasks as $version => $versionTasks) {
            foreach($versionTasks as $task) {
                $definition->addMethodCall(
                    'addTask',
                    array($version,$task['command'],$task['parameters'],$task['salt'])
                );
            }
        }
    }
}