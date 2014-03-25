<?php

namespace Nucleus\Bundle\MigrationBundle;

use Nucleus\Bundle\MigrationBundle\DependencyInjection\MigrationCompilerPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class NucleusMigrationBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new MigrationCompilerPass());
    }
}