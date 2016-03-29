<?php

namespace BulutYazilim\OjsDoiBundle\DependencyInjection;

use BulutYazilim\OjsDoiBundle\Importer\CrossrefCommand;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class OjsDoiExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $bundles = $container->getParameter('kernel.bundles');
        if (array_key_exists('ImportBundle', $bundles)) {
            $container
                ->register('ojs.doi.crossref_command', CrossrefCommand::class)
                ->addTag('console.command');
        }
    }
}
