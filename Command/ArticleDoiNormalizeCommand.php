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
 * Class ArticleDoiNormalizeCommand
 * @package BulutYazilim\OjsDoiBundle\Command
 */
class ArticleDoiNormalizeCommand extends ContainerAwareCommand
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
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var int
     */
    private $totalArticleCount;

    /**
     * @var string
     */
    private $doiStartYear;

    /**
     * @var bool
     */
    private $validate;

    /**
     * @var bool
     */
    private $checkRequests;

    /**
     * @var array
     */
    private $crossrefConfigs = [];

    /**
     * @var array
     */
    private $crossrefConfigIdBag = [];

    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('ojs:article:doi:normalize')
            ->addOption('validate', 'va', InputOption::VALUE_NONE, 'Validate Dois too')
            ->addOption('check-requests', 'cr', InputOption::VALUE_NONE, 'Checks requested dois are valid')
            ->setDescription('Normalize articles doi and doi statuses.')
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
        $this->translator                   = $this->container->get('translator');
        $this->totalArticleCount            = $this->getTotalArticleCount();
        $this->doiStartYear                 = $this->container->getParameter('doi_start_year');
        $this->validate                     = $input->getOption('validate');
        $this->checkRequests                = $input->getOption('check-requests');
        $this->crossrefConfigs              = $this->getCrossrefConfigs();
        $this->crossrefConfigJournalIdBag   = $this->getCrossrefJournalIdBag();
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
        $this->io->progressStart($this->totalArticleCount);
        foreach(range(0, $this->totalArticleCount, 100) as $number){
            $fetchArticles = $this->em->getRepository('OjsJournalBundle:Article')->findBy([], null, 100, $number);
            $this->normalizeDoiStatuses($fetchArticles);
            $this->io->progressAdvance(100);
            $this->em->flush();
        }
    }

    /**
     * @return int
     */
    private function getTotalArticleCount()
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('count(article.id)');
        $qb->from('OjsJournalBundle:Article','article');

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $articles
     */
    private function normalizeDoiStatuses($articles)
    {
        /** @var Article $article */
        foreach($articles as $article){
            if(!$this->getCrossrefConfigByJournal($article->getJournal()) instanceof CrossrefConfig){
                if(empty($article->getDoi())){
                    $article->setDoiStatus(DoiStatuses::NOT_AVAILABLE);
                    $this->em->persist($article);
                    continue;
                }
                if(!$this->validate){
                    continue;
                }
                if($this->doiIsValid($article->getDoi())){
                    $article->setDoiStatus(DoiStatuses::VALID);
                }else{
                    $article->setDoi(null);
                    $article->setDoiStatus(DoiStatuses::NOT_AVAILABLE);
                }
            }
            //if article doi status is set as valid
            if($article->getStatus() == DoiStatuses::VALID){
                //if doi is empty
                if(empty($article->getDoi())){
                    //if article pudate is greater than system doi start date
                    if($article->getPubdate()->format('Y') >= $this->doiStartYear){
                        //set status as not requested
                        $article->setDoiStatus(DoiStatuses::NOT_REQUESTED);
                    }else{
                        //set status as not available
                        $article->setDoiStatus(DoiStatuses::NOT_AVAILABLE);
                    }
                    continue;
                }
                //if cli specified validate option
                if($this->validate){
                    //validate doi if doi is not valid
                    if(!$this->doiIsValid($article->getDoi())){
                        //if not valid set as null for doi
                        $article->setDoi(null);
                        //set status according to doi getting strategy
                        if($article->getPubdate()->format('Y') >= $this->doiStartYear){
                            $article->setDoiStatus(DoiStatuses::NOT_REQUESTED);
                        }else{
                            $article->setDoiStatus(DoiStatuses::NOT_AVAILABLE);
                        }
                    }
                }
                //persist article
                $this->em->persist($article);
                continue;
            }
            //if doi is requested
            if($article->getStatus() == DoiStatuses::REQUESTED){
                //if doi field is empty set status as NOT_REQUESTED or NOT_AVAILABLE
                if(empty($article->getDoi())){
                    if($article->getPubdate()->format('Y') >= $this->doiStartYear){
                        $article->setDoiStatus(DoiStatuses::NOT_REQUESTED);
                    }else{
                        $article->setDoiStatus(DoiStatuses::NOT_AVAILABLE);
                    }
                }
                //if doi is not empty and cli specified as check requests validate doi and set as valid
                if(!empty($article->getDoi()) && $this->checkRequests){
                    if($this->doiIsValid($article->getDoi())){
                        $article->setDoiStatus(DoiStatuses::VALID);
                    }
                }
                $this->em->persist($article);
                continue;
            }
            //if doi is not available
            if($article->getStatus() == DoiStatuses::NOT_AVAILABLE){
                if(!empty($article->getDoi())){
                    if($this->doiIsValid($article->getDoi())){
                        $article->setDoiStatus(DoiStatuses::VALID);
                    }else{
                        if($article->getPubdate()->format('Y') >= $this->doiStartYear){
                            $article->setDoiStatus(DoiStatuses::NOT_REQUESTED);
                        }
                    }
                }
                if(empty($article->getDoi())){
                    if($article->getPubdate()->format('Y') >= $this->doiStartYear){
                        $article->setDoiStatus(DoiStatuses::NOT_REQUESTED);
                    }
                }
                $this->em->persist($article);
                continue;
            }
            //if doi is not requested
            if($article->getStatus() == DoiStatuses::NOT_REQUESTED){
                if(!empty($article->getDoi())){
                    if($this->doiIsValid($article->getDoi())){
                        $article->setDoiStatus(DoiStatuses::VALID);
                    }else{
                        if($article->getPubdate()->format('Y') < $this->doiStartYear){
                            $article->setDoiStatus(DoiStatuses::NOT_AVAILABLE);
                        }
                    }
                }
                if(empty($article->getDoi())){
                    if($article->getPubdate()->format('Y') < $this->doiStartYear){
                        $article->setDoiStatus(DoiStatuses::NOT_AVAILABLE);
                    }
                }
                $this->em->persist($article);
                continue;
            }
        }

        return;
    }

    /**
     * @return array
     */
    private function getCrossrefConfigs()
    {
        return $this->em->getRepository('OjsDoiBundle:CrossrefConfig')->findAll();
    }

    /**
     * @return array
     */
    private function getCrossrefConfigByJournal(Journal $journal)
    {
        /** @var CrossrefConfig $result */
        foreach($this->crossrefConfigs as $result){
            if($journal->getId() == $result->getJournal()->getId() && $result->isValid()){
                return $result;
            }
        }
        return;
    }

    /**
     * @return array
     */
    private function getCrossrefJournalIdBag()
    {
        $ids = [];
        foreach($this->crossrefConfigs as $result){
            $ids[] = $result->getJournal()->getId();
        }
        return $ids;
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
