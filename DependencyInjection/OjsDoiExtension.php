<?php

namespace Vipa\DoiBundle\DependencyInjection;

use Vipa\DoiBundle\Importer\CrossrefImportCommand;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class VipaDoiExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $bundles = $container->getParameter('kernel.bundles');
        if (array_key_exists('ImportBundle', $bundles)) {
            $container
                ->register('vipa.doi.crossref_import_command', CrossrefImportCommand::class)
                ->addTag('console.command');
        }
    }
}
