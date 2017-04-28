<?php

namespace Vipa\DoiBundle\EventListener;

use FOS\UserBundle\Model\UserInterface;
use Vipa\CoreBundle\Acl\AuthorizationChecker;
use Vipa\JournalBundle\Event\MenuEvent;
use Vipa\JournalBundle\Event\MenuEvents;
use Vipa\JournalBundle\Service\JournalService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LeftMenuListener implements EventSubscriberInterface
{
    /**
     * @var  AuthorizationChecker
     */
    private $checker;

    /**
     * @var  JournalService
     */
    private $journalService;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * LeftMenuListener constructor.
     * @param AuthorizationChecker $checker
     * @param JournalService $journalService
     */
    public function __construct(AuthorizationChecker $checker, JournalService $journalService, TokenStorageInterface $tokenStorage)
    {
        $this->checker          = $checker;
        $this->journalService   = $journalService;
        $this->tokenStorage     = $tokenStorage;
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
        $user = $this->tokenStorage->getToken()->getUser();

        if($user === null || ($user instanceof UserInterface && !$user->isSuperAdmin())){
            return;
        }
        $menuItem = $menuEvent->getMenuItem();
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
