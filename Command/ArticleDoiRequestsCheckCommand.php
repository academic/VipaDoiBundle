<?php

namespace BulutYazilim\OjsDoiBundle\Command;

use BulutYazilim\OjsDoiBundle\Entity\CrossrefConfig;
use Doctrine\ORM\EntityManager;
use Ojs\CoreBundle\Params\ArticleStatuses;
use Ojs\CoreBundle\Params\DoiStatuses;
use Ojs\JournalBundle\Entity\Article;
use Ojs\JournalBundle\Entity\Journal;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\TranslatorInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client;

/**
 * Class ArticleDoiRequestsCheckCommand
 * @package BulutYazilim\OjsDoiBundle\Command
 */
class ArticleDoiRequestsCheckCommand extends ContainerAwareCommand
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('ojs:article:doi:requests:check')
            ->setDescription('Article doi requests check.')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io                           = new SymfonyStyle($input, $output);
        $this->container                    = $this->getContainer();
        $this->em                           = $this->container->get('doctrine')->getManager();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title($this->getDescription());
        $fetchDoiRequestedArticles = $this->em->getRepository('OjsJournalBundle:Article')->findBy([
            'doiStatus' => DoiStatuses::REQUESTED
        ]);
        $this->io->progressStart(count($fetchDoiRequestedArticles));
        $this->io->newLine();
        /** @var Article $article */
        foreach($fetchDoiRequestedArticles as $article){
            if($this->doiIsValid($article->getDoi())){
                $article->setDoiStatus(DoiStatuses::VALID);
            }else{
                $article->setLastDoiCheck((new \DateTime()));
            }
        }
        $this->em->persist($article);
        $this->em->flush();
        $this->io->success('Finished all article doi requests check');
    }

    /**
     * @param $doi
     *
     * @return bool
     */
    private function doiIsValid($doi)
    {
        try {
            $client = new Client();
            $client->get('http://doi.org/api/handles/'.$doi);
            $this->io->writeln('valid doi ####> '.$doi);
            return true;
        } catch(\Exception $e) {
            $this->io->writeln('invalid doi ----> '.$doi);
            return false;
        }
    }
}
