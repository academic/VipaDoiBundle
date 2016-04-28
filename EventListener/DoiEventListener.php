<?php

namespace BulutYazilim\OjsDoiBundle\EventListener;

use Doctrine\Common\Persistence\ObjectManager;
use Ojs\CoreBundle\Events\TwigEvent;
use Ojs\JournalBundle\Service\JournalService;
use Ojs\JournalBundle\Entity\Journal;
use Ojs\CoreBundle\Event\WorkflowEvent;
use Ojs\CoreBundle\Events\TwigEvents;
use Ojs\UserBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DoiEventListener implements EventSubscriberInterface
{
    /** @var  ObjectManager */
    private $em;

    /** @var  RouterInterface */
    private $router;

    /** @var  JournalService */
    private $journalService;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var \Twig_Environment */
    private $twig;

    /**
     * @param ObjectManager   $em
     * @param RouterInterface $router
     * @param JournalService  $journalService
     */
    public function __construct(
        ObjectManager $em,
        RouterInterface $router,
        JournalService $journalService,
        TokenStorageInterface $tokenStorage,
        \Twig_Environment $twig
    )
    {
        $this->em = $em;
        $this->router = $router;
        $this->journalService = $journalService;
        $this->tokenStorage = $tokenStorage;
        $this->twig = $twig;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            TwigEvents::OJS_ARTICLE_SHOW_VIEW => 'onArticleShowView',
            TwigEvents::OJS_ARTICLE_EDIT_VIEW => 'onArticleEditView',
        );
    }

    /**
     * @param TwigEvent $event
     */
    public function onArticleShowView(TwigEvent $event)
    {
        return $this->generateGetDoiButton($event);
    }

    /**
     * @param TwigEvent $event
     */
    public function onArticleEditView(TwigEvent $event)
    {
        return $this->generateGetDoiButton($event);
    }

    private function generateGetDoiButton(TwigEvent $event)
    {
        $journal = $this->journalService->getSelectedJournal();
        $crossrefConfig = $this->em->getRepository('OjsDoiBundle:CrossrefConfig')->findOneBy(array('journal' => $journal));
        if(!$crossrefConfig || !$crossrefConfig->isValid()) {
            return;
        }
        $entity = $event->getOptions()['entity'];
        $template = $this->twig->render('@OjsDoi/Article/get_doi_button.html.twig', [
            'entity'=> $entity,
            'journal' => $journal,
        ]);
        $event->setTemplate($template);
    }
}
