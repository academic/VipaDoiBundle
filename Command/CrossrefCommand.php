<?php

namespace BulutYazilim\OjsDoiBundle\Command;

use BulutYazilim\OjsDoiBundle\Entity\CrossrefConfig;
use BulutYazilim\OjsDoiBundle\Entity\DoiStatus;
use GuzzleHttp\Client;
use Ojs\CoreBundle\Params\DoiStatuses;
use Ojs\JournalBundle\Entity\Article;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManager;

class CrossrefCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('ojs:crossref:check')
            ->setDescription('Crossref check')
            ->setAliases(['crossref'])
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $logger = $this->getContainer()->get('logger');
        $doiStatusRepo = $em->getRepository(DoiStatus::class);
        $configRepo = $em->getRepository(CrossrefConfig::class);
        $output->writeln('checking...');
        $i = 0;
        while($i < 30) {
            $date = new \DateTime();

            /** @var DoiStatus[] $doiStatuses */
            $doiStatuses = $doiStatusRepo->createQueryBuilder('ds')
                ->leftJoin('ds.article', 'a')
                ->andWhere('a.doiStatus != :doiStatus')
                ->andWhere('ds.status = :status')
                ->andWhere('ds.createdAt > :date')
                ->setParameter('date', $date->modify('-1 hours'))
                ->setParameter('status', 'submitted')
                ->setParameter('doiStatus', DoiStatuses::VALID)
                ->getQuery()
                ->getResult();
            foreach ($doiStatuses as $doiStatus) {
                $output->writeln($doiStatus->getBatchId());

                /** @var Article $article */
                $article = $doiStatus->getArticle();
                $config = $configRepo->findOneBy(['journal' => $article->getJournal()]);

                try {
                    $client = new Client(
                        [
                            'base_uri' => 'https://api.crossref.org/'
                            ,
                            'timeout' => 0,
                            'auth' => [$config->getUsername(), $config->getPassword()]
                        ]
                    );
                    $response = $client->request('GET', 'deposits/'.$doiStatus->getBatchId());
                } catch (\Exception $e) {
                    $logger->alert(sprintf('doi-failed: %s', $doiStatus->getBatchId()));

                    continue;
                }


                $responseObject = json_decode($response->getBody()->getContents(), true);

                $message = $responseObject['message'];
                if (!array_key_exists('submission', $message)) {
                    $logger->alert(sprintf('doi-submission-not-found: %s', $doiStatus->getBatchId()));

                    continue;
                }
                if($message['status'] === 'submitted') {
                    continue;
                }
                elseif($message['status'] === 'failed') {
                    $article->setDoiStatus(DoiStatuses::INVALID);
                    $em->persist($article);
                }

                $doiStatus->setStatus($message['status']);
                $doiStatus->setDescription($message['submission']['messages'][0]['message']);
                $em->persist($doiStatus);

                $logger->info(
                    sprintf('doi: %s %s %s', $doiStatus->getBatchId(), $config->getUsername(), $config->getPassword())
                );
            }

            $em->flush();
            sleep(10);
            $i++;
        }

    }
}
