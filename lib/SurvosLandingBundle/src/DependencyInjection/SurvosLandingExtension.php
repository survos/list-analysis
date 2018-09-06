<?php

namespace Survos\LandingBundle\DependencyInjection;

use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigManager;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class SurvosLandingExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);
        $definition = $container->getDefinition('survos_landing_bundle.landing_service');

        $definition->setArgument(0, $config['entities']);

        // $configManager = $container->get('easyadmin.config.manager');
        // $definition->setArgument(1, $configManager);
        // $definition->setArgument(1, $config['min_sunshine']);
    }

    public function getAlias()
    {
        return 'survos_landing';
    }
}
