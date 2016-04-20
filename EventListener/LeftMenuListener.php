<?php

namespace BulutYazilim\OjsDoiBundle\EventListener;

use Ojs\CoreBundle\Acl\AuthorizationChecker;
use Ojs\JournalBundle\Event\MenuEvent;
use Ojs\JournalBundle\Event\MenuEvents;
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
            MenuEvents::LEFT_MENU_INITIALIZED => 'onLeftMenuInitialized',
        );
    }

    /**
     * @param MenuEvent $menuEvent
     */
    public function onLeftMenuInitialized(MenuEvent $menuEvent)
    {
        $journal = $this->journalService->getSelectedJournal();

        $menuItem = $menuEvent->getMenuItem();
        if ($this->checker->isGranted('EDIT', $journal)) {
            $menuItem->addChild(
                'doi.config.title',
                [
                    'route' => 'bulut_yazilim_doi_config_edit',
                    'routeParameters' => [
                        'journalId' => $journal->getId()
                    ]

                ]
            );
        }
    }

}
