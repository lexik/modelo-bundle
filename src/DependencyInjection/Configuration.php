<?php

namespace Choosit\ModeloBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('modelo');

        $treeBuilder
            ->getRootNode()
                ->children()
                    ->scalarNode('base_uri')->info('Modelo API base URI')->defaultNull()->cannotBeEmpty()->end()
                ->end()
                ->children()
                    ->arrayNode('auth')
                        ->children()
                            ->scalarNode('agency_code')->info('Agency code you can find on your modelo account in "Integration in an external tool".')->defaultNull()->end()
                            ->scalarNode('private_key')->info('Private key you can find on your modelo account in "Integration in an external tool".')->defaultNull()->end()
                        ->end()
                    ->end()
                ->end();

        return $treeBuilder;
    }
}
