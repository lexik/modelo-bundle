<?php

namespace Choosit\ModeloBundle\DependencyInjection;

use Exception;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class ModeloExtension extends Extension
{
    /**
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('modelo_config', $config);

        $this->registerModeloConfiguration($config, $container);
    }

    private function registerModeloConfiguration(array $config, ContainerBuilder $container): void
    {
        $this->registerHttpClientDefinitions($container);
        $this->registerServiceDefinitions($container);

        $httpClientDefinition = $container->getDefinition('choosit_modelo.http_client');
        $definition = $container->getDefinition('choosit_modelo.modelo_http_client');

        if (!isset($config['base_uri'])) {
            throw new InvalidConfigurationException('base_uri parameter must be defined.');
        }

        $httpClientDefinition->replaceArgument(0, ['base_uri' => $config['base_uri']]);

        $definition->replaceArgument(0, $httpClientDefinition);
        $definition->replaceArgument(1, $config['auth']['agency_code'] ?? null);
        $definition->replaceArgument(2, $config['auth']['private_key'] ?? null);
    }

    private function registerServiceDefinitions(ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
    }

    private function registerHttpClientDefinitions(ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('http_client.php');
    }
}
