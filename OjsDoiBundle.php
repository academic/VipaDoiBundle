<?php

namespace OkulBilisim\OjsDoiBundle;

use OkulBilisim\OjsDoiBundle\DependencyInjection\Compiler\RabbitMqCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OjsDoiBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RabbitMqCompilerPass());
    }
}
