<?php

declare(strict_types=1);

namespace Rafalli\DpdInfoServicesBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('rafalli_dpd_info_services');

        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        $nodeBuilder = $rootNode->children();

        $nodeBuilder
            ->scalarNode('wsdl_url')
                ->defaultValue('https://dpdinfoservices.dpd.com.pl/DPDInfoServicesObjEventsService/DPDInfoServicesObjEvents?wsdl')
                ->info('WSDL address for production or test environment');

        $nodeBuilder
            ->scalarNode('channel')
                ->isRequired()
                ->cannotBeEmpty()
                ->info('Channel (FID) assigned by DPD');

        $nodeBuilder
            ->scalarNode('username')
                ->isRequired()
                ->cannotBeEmpty()
                ->info('DPD InfoServices API login');

        $nodeBuilder
            ->scalarNode('password')
                ->isRequired()
                ->cannotBeEmpty()
                ->info('DPD InfoServices API password');

        return $treeBuilder;
    }
}
