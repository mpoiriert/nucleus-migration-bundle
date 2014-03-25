<?php

namespace Nucleus\Bundle\MigrationBundle\DependencyInjection;

use \Symfony\Component\HttpKernel\DependencyInjection\Extension;
use \Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

class NucleusMigrationExtension extends Extension
{
    /**
     * Handles the knp_menu configuration.
     *
     * @param array            $configs   The configurations being loaded
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $fileLocator = new FileLocator(__DIR__.'/../Resources/config');
        $loader = new XmlFileLoader($container, $fileLocator);
        $loader->load('migration.xml');

        $configuration = $this->getConfiguration($configs, $container);

        $config = $this->processConfiguration($configuration,$configs);

        $container->setParameter('nucleus_migration.versions',$config['versions']);
        $container->setParameter('nucleus_migration.tasks',$config['tasks']);
    }

    public function getAlias()
    {
        return 'nucleus_migration';
    }
}