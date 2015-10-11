<?php

namespace OkulBilisim\OjsDoiBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class RabbitMqCompilerPass implements CompilerPassInterface
{
    private $configs = array(
        'connections' => array(
            'default' =>
                array(
                    'host' => 'localhost',
                    'port' => 5672,
                    'user' => 'guest',
                    'password' => 'guest',
                    'vhost' => '/',
                    'lazy' => false,
                    'connection_timeout' => 3,
                    'read_write_timeout' => 3,
                    # requires php-amqplib v2.4.1+ and PHP5.4+
                    'keepalive' => true,
                    # requires php-amqplib v2.4.1+
                    'heartbeat' => 0
                )
        ),
        'producers' => array(
            'doi_status' => array(
                'connection' => 'default',
                'exchange_options' => array(
                    'name' => 'doi_queue',
                    'type' => 'direct'
                )
            )
        ),
        'consumers' => array(
            'save_doi_status' => array(
                'connection' => 'default',
                'exchange_options' => array(
                    'name' => 'doi_queue',
                    'type' => 'direct'
                ),
                'queue_options' => array(
                    'name' => 'doi_queue'
                ),
                'callback' => 'doi.consumer'
            )
        )
    );

    public function process(ContainerBuilder $container)
    {
        foreach ($this->configs['connections'] as $key => $connection) {
            $classParam =
                $connection['lazy']
                    ? '%old_sound_rabbit_mq.lazy.connection.class%'
                    : '%old_sound_rabbit_mq.connection.class%';

            $definition = new Definition(
                '%old_sound_rabbit_mq.connection_factory.class%', array(
                    $classParam,
                    $connection,
                )
            );
            $definition->setPublic(false);
            $factoryName = sprintf('old_sound_rabbit_mq.connection_factory.%s', $key);
            $container->setDefinition($factoryName, $definition);

            $definition = new Definition($classParam);

            $definition->setFactory(array(new Reference($factoryName), 'createConnection'));


            $container->setDefinition(sprintf('old_sound_rabbit_mq.connection.%s', $key), $definition);
        }

        foreach ($this->configs['producers'] as $key => $producer) {

            $definition = new Definition($container->getParameter('old_sound_rabbit_mq.producer.class'));
            $definition->addTag('old_sound_rabbit_mq.base_amqp');
            $definition->addTag('old_sound_rabbit_mq.producer');
            //this producer doesn't define an exchange -> using AMQP Default
            if (!isset($producer['exchange_options'])) {
                $producer['exchange_options']['name'] = '';
                $producer['exchange_options']['type'] = 'direct';
                $producer['exchange_options']['passive'] = true;
                $producer['exchange_options']['declare'] = false;
            }

            $definition->addMethodCall(
                'setExchangeOptions',
                array($producer['exchange_options'])
            );
            //this producer doesn't define a queue
            if (!isset($producer['queue_options'])) {
                $producer['queue_options']['name'] = null;
            }
            $definition->addMethodCall('setQueueOptions', array($producer['queue_options']));
            $definition->addArgument(
                new Reference(sprintf('old_sound_rabbit_mq.connection.%s', $producer['connection']))
            );

            $definition->addMethodCall('disableAutoSetupFabric');

            $container->setDefinition(sprintf('old_sound_rabbit_mq.%s_producer', $key), $definition);
        }

        foreach ($this->configs['consumers'] as $key => $consumer) {
            $definition = new Definition('%old_sound_rabbit_mq.consumer.class%');
            $definition
                ->addTag('old_sound_rabbit_mq.base_amqp')
                ->addTag('old_sound_rabbit_mq.consumer')
                ->addMethodCall(
                    'setExchangeOptions',
                    array($consumer['exchange_options'])
                )
                ->addMethodCall('setQueueOptions', array($consumer['queue_options']))
                ->addMethodCall('setCallback', array(array(new Reference($consumer['callback']), 'execute')));

            if (array_key_exists('qos_options', $consumer)) {
                $definition->addMethodCall(
                    'setQosOptions',
                    array(
                        $consumer['qos_options']['prefetch_size'],
                        $consumer['qos_options']['prefetch_count'],
                        $consumer['qos_options']['global']
                    )
                );
            }

            if (isset($consumer['idle_timeout'])) {
                $definition->addMethodCall('setIdleTimeout', array($consumer['idle_timeout']));
            }
            $definition->addMethodCall('disableAutoSetupFabric');

            $definition->addArgument(
                new Reference(sprintf('old_sound_rabbit_mq.connection.%s', $consumer['connection']))
            );

            $name = sprintf('old_sound_rabbit_mq.%s_consumer', $key);
            $container->setDefinition($name, $definition);

            $callbackDefinition = $container->findDefinition($consumer['callback']);
            $refClass = new \ReflectionClass($callbackDefinition->getClass());
            if ($refClass->implementsInterface('OldSound\RabbitMqBundle\RabbitMq\DequeuerAwareInterface')) {
                $callbackDefinition->addMethodCall('setDequeuer', array(new Reference($name)));
            }
        }
    }
}
