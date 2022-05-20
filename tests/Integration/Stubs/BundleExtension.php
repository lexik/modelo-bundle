<?php

namespace Choosit\ModeloBundle\Tests\Integration\Stubs;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class BundleExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $phpLoader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../config'));
        $phpLoader->load('services.php');
    }
}
