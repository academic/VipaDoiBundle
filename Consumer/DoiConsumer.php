<?php

namespace BulutYazilim\OjsDoiBundle\Consumer;

use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;


class DoiConsumer implements ConsumerInterface
{
    /** @var EntityManager */
    private $em;

    /** @var LoggerInterface */
    private $logger;

    /**
     * DoiConsumer constructor.
     *
     * @param EntityManager $em
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManager $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    public function execute(AMQPMessage $msg)
    {

        try {
            list($batchId, $username, $password) = unserialize($msg->body);
            if (!$batchId) {
                throw new \Exception('batchId not found');
            }
            $doiStatus = $this->em->getRepository('OjsDoiBundle:DoiStatus')->findOneBy(array('batchId' => $batchId));
            if (!$doiStatus) {
                throw new \Exception('doiStatus not found');
            }
        } catch (\Exception $e) {
            $this->logger->alert(sprintf('doi-error: %s %s', $e->getMessage(), $msg->body));

            return;
        }

        sleep(5 * 60);

        $client = new Client(
            [
                'base_uri' => 'https://api.crossref.org/'
                ,
                'timeout' => 0,
                'auth' => [$username, $password]
            ]
        );

        $response = $client->request('GET', 'deposits/'.$batchId);

        $responseObject = json_decode($response->getBody()->getContents(), true);

        $message = $responseObject['message'];
        if (!array_key_exists('submission', $message)) {
            $this->logger->alert(sprintf('doi-submission-not-found: %s', $batchId));

            return;
        }
        $doiStatus->setStatus($message['status']);
        $doiStatus->setDescription($message['submission']['messages'][0]['message']);
        $this->em->persist($doiStatus);
        $this->em->flush();

        $this->logger->info(sprintf('doi: %s %s %s', $batchId, $username, $password));
    }
}
