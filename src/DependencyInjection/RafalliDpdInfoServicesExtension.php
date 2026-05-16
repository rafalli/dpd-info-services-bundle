<?php

declare(strict_types=1);

namespace Rafalli\DpdInfoServicesBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class RafalliDpdInfoServicesExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('rafalli_dpd_info_services.wsdl_url', $config['wsdl_url']);
        $container->setParameter('rafalli_dpd_info_services.channel', $config['channel']);
        $container->setParameter('rafalli_dpd_info_services.username', $config['username']);
        $container->setParameter('rafalli_dpd_info_services.password', $config['password']);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../config')
        );
        $loader->load('services.yaml');
    }
}
