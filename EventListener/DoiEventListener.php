<?php

namespace BulutYazilim\OjsDoiBundle\EventListener;

use BulutYazilim\OjsDoiBundle\Entity\CrossrefConfig;
use BulutYazilim\OjsDoiBundle\Service\DoiGenerator;
use Doctrine\Common\Persistence\ObjectManager;
use Ojs\CoreBundle\Events\TwigEvent;
use Ojs\AdminBundle\Events\StatEvent;
use Ojs\CoreBundle\Params\DoiStatuses;
use Ojs\JournalBundle\Entity\Article;
use Ojs\JournalBundle\Entity\Journal;
use Ojs\JournalBundle\Event\JournalEvent;
use Ojs\JournalBundle\Event\JournalItemEvent;
use Ojs\JournalBundle\Service\JournalService;
use Ojs\CoreBundle\Events\TwigEvents;
use Ojs\AdminBundle\Events\StatEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DoiEventListener implements EventSubscriberInterface
{
    /**
     * @var  ObjectManager
     */
    private $em;

    /**
     * @var  RouterInterface
     */
    private $router;

    /**
     * @var  JournalService
     */
    private $journalService;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var string
     */
    private $doiStartYear;

    /**
     * @var  DoiGenerator
     */
    private $doiGenerator;

    /**
     * DoiEventListener constructor.
     * @param ObjectManager $em
     * @param RouterInterface $router
     * @param JournalService $journalService
     * @param TokenStorageInterface $tokenStorage
     * @param \Twig_Environment $twig
     * @param $doiStartYear
     * @param DoiGenerator $doiGenerator
     */
    public function __construct(
        ObjectManager $em,
        RouterInterface $router,
        JournalService $journalService,
        TokenStorageInterface $tokenStorage,
        \Twig_Environment $twig,
        $doiStartYear,
        DoiGenerator $doiGenerator
    )
    {
        $this->em               = $em;
        $this->router           = $router;
        $this->journalService   = $journalService;
        $this->tokenStorage     = $tokenStorage;
        $this->twig             = $twig;
        $this->doiStartYear     = $doiStartYear;
        $this->doiGenerator     = $doiGenerator;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            TwigEvents::OJS_ARTICLE_SHOW_VIEW       => 'onArticleShowView',
            TwigEvents::OJS_ARTICLE_EDIT_VIEW       => 'onArticleEditView',
            TwigEvents::OJS_ADMIN_STATS_DOI_TABS    => 'onAdminStatsDoiTabs',
            TwigEvents::OJS_ADMIN_STATS_DOI_CONTENT => 'onAdminStatsDoiContent',
            StatEvents::OJS_ADMIN_STATS_CACHE       => 'onAdminStatsCache',
            TwigEvents::OJS_ADMIN_STATS_DOI_SCRIPT  => 'onAdminStatsDoiScript',
            'get.journal.crossref.config'           => 'onJournalCrossrefConfigRequest',
            'generate.article.doi'                  => 'onArticleDoiGenerateRequest',
        );
    }

    /**
     * @param TwigEvent $event
     * @return null
     */
    public function onArticleShowView(TwigEvent $event)
    {
        return $this->generateGetDoiButton($event);
    }

    /**
     * @param TwigEvent $event
     * @return null
     */
    public function onArticleEditView(TwigEvent $event)
    {
        return $this->generateGetDoiButton($event);
    }

    /**
     * @param TwigEvent $event
     * @return null
     */
    public function onAdminStatsDoiTabs(TwigEvent $event)
    {
        $template = $this->twig->render('@OjsDoi/Admin/stats_tabs.html.twig');
        $event->setTemplate($template);

        return;
    }

    /**
     * @param TwigEvent $event
     * @return null
     */
    public function onAdminStatsDoiContent(TwigEvent $event)
    {

        $template = $this->twig->render('@OjsDoi/Admin/stats_content.html.twig',[
            'doiArticleMonthly' => $event->getOptions()['doi_article_monthly'],
            'doiArticleYearly' => $event->getOptions()['doi_article_yearly'],
        ]);
        $event->setTemplate($template);

        return;
    }

    /**
     * @param StatEvent $event
     * @return null
     */
    public function onAdminStatsCache(StatEvent $event)
    {
        $json = $event->getJson();
        $data = $event->getData();

        $json['doiArticle'] = $this->doiGenerator->generateDoiArticleBarChartData();
        $data['doiArticleMonthly'] = $this->doiGenerator->generateDoiArticleMonthlyData();
        $data['doiArticleYearly'] = $this->doiGenerator->generateDoiArticleYearlyData();

        $event->setJson($json);
        $event->setData($data);

        return;
    }

    /**
     * @param TwigEvent $event
     * @return null
     */
    public function onAdminStatsDoiScript(TwigEvent $event)
    {
        $event->setTemplate("analytics.createApplicationChart('#doiArticleChart', data['doiArticle']);");

        return;
    }

    /**
     * @param TwigEvent $event
     * @return null
     */
    private function generateGetDoiButton(TwigEvent $event)
    {
        $journal = $this->journalService->getSelectedJournal();
        $crossrefConfig = $this->em->getRepository('OjsDoiBundle:CrossrefConfig')->findOneBy(array('journal' => $journal));
        if(!$crossrefConfig || !$crossrefConfig->isValid()) {
            return;
        }
        /** @var Article $entity */
        $entity = $event->getOptions()['entity'];
        if($entity->getDoiStatus() == DoiStatuses::VALID || $entity->getPubdate() === null || $entity->getPubdate()->format('Y') < $this->doiStartYear ){
            return;
        }
        $template = $this->twig->render('@OjsDoi/Article/get_doi_button.html.twig', [
            'entity'=> $entity,
            'journal' => $journal,
        ]);
        $event->setTemplate($template);

        return;
    }

    /**
     * @param JournalEvent $event
     */
    public function onJournalCrossrefConfigRequest(JournalEvent $event)
    {
        $journal = $event->getJournal();
        if(!$journal instanceof Journal){
            return;
        }
        $crossrefConfig = $this->em->getRepository(CrossrefConfig::class)->findOneBy([
            'journal' => $journal
        ]);
        if(!$crossrefConfig || !$crossrefConfig->isValid()) {
            return;
        }
        $crossrefConfigArray = [
            'username' => $crossrefConfig->getUsername(),
            'password' => $crossrefConfig->getPassword(),
            'email' => $crossrefConfig->getEmail(),
            'fullName' => $crossrefConfig->getFullName(),
        ];
        $journal->setExtraFields([
            'crossrefConfig' => $crossrefConfigArray
        ]);
    }

    /**
     * @param JournalItemEvent $event
     */
    public function onArticleDoiGenerateRequest(JournalItemEvent $event)
    {
        $article = $event->getItem();
        if(!$article instanceof Article){
            return;
        }
        if(!empty($article->getDoi())){
            return;
        }
        $doi = $this->doiGenerator->generate($article);
        $article->setDoi($doi);
    }
}
