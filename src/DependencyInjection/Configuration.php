<?php

declare(strict_types=1);

namespace Rafalli\DpdInfoServicesBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('rafalli_dpd_info_services');

        $treeBuilder->getRootNode()
            ->children()
            ->scalarNode('wsdl_url')
            ->defaultValue('https://dpdservices.dpd.com.pl/DPDInfoServicesObjEventsService/DPDInfoServicesObjEvents?wsdl')
            ->info('WSDL address for production or test environment')
            ->end()
            ->scalarNode('channel')
            ->isRequired()
            ->cannotBeEmpty()
            ->info('Channel (Customer Information Channel ID) assigned by DPD')
            ->end()
            ->scalarNode('username')
            ->isRequired()
            ->cannotBeEmpty()
            ->info('DPD InfoServices API login')
            ->end()
            ->scalarNode('password')
            ->isRequired()
            ->cannotBeEmpty()
            ->info('DPD InfoServices API password')
            ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
