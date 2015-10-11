<?php

namespace OkulBilisim\OjsDoiBundle\Consumer;


use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;


class DoiConsumer implements ConsumerInterface
{

    /** @var LoggerInterface */
    private $logger;

    /**
     * TcknConsumer constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function execute(AMQPMessage $msg)
    {

        $this->logger->info(sprintf('doi: "%s"', $msg->body));
    }
}
