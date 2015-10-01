<?php

namespace OkulBilisim\OjsDoiBundle\EventListener;

use Ojs\CoreBundle\Acl\AuthorizationChecker;
use Ojs\JournalBundle\Event\MenuEvent;
use Ojs\JournalBundle\JournalEvents;
use Ojs\JournalBundle\Service\JournalService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LeftMenuListener implements EventSubscriberInterface
{
    /** @var  AuthorizationChecker */
    private $checker;

    /** @var  JournalService */
    private $journalService;

    /**
     * LeftMenuListener constructor.
     * @param AuthorizationChecker $checker
     * @param JournalService $journalService
     */
    public function __construct(AuthorizationChecker $checker, JournalService $journalService)
    {
        $this->checker = $checker;
        $this->journalService = $journalService;
    }


    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            JournalEvents::LEFT_MENU_INITIALIZED => 'onLeftMenuInitialized',
        );
    }

    /**
     * @param MenuEvent $menuEvent
     */
    public function onLeftMenuInitialized(MenuEvent $menuEvent)
    {
        $journal = $this->journalService->getSelectedJournal();
        $journalId = $journal->getId();

        $menuItem = $menuEvent->getMenuItem();
        if ($this->checker->isGranted('EDIT', $journal)) {
            $menuItem->addChild(
                'doi.config.title',
                [
                    'route' => 'okul_bilisim_doi_config_edit',
                    'routeParameters' => ['journalId' => $journalId]
                ]
            );
        }
    }

}
