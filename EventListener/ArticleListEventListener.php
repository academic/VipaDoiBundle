<?php

namespace OkulBilisim\OjsDoiBundle\EventListener;

use APY\DataGridBundle\Grid\Action\RowAction;
use APY\DataGridBundle\Grid\Column\ActionsColumn;
use Ojs\JournalBundle\Event\ListEvent;
use Ojs\JournalBundle\JournalEvents;
use Ojs\JournalBundle\Service\JournalService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ArticleListEventListener implements EventSubscriberInterface
{
    /** @var  JournalService */
    private $journalService;

    /**
     * ArticleListEventListener constructor.
     * @param JournalService $journalService
     */
    public function __construct(JournalService $journalService)
    {
        $this->journalService = $journalService;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            JournalEvents::ARTICLE_LIST_INITIALIZED => 'onListInitialized',
        );
    }

    /**
     * @param ListEvent $event
     */
    public function onListInitialized(ListEvent $event)
    {
        $journal = $this->journalService->getSelectedJournal();
        $grid = $event->getGrid();

        /** @var ActionsColumn $actionColumn */
        $actionColumn = $grid->getColumn("actions");
        $rowActions = $actionColumn->getRowActions();

        $rowAction = new RowAction('<i class="fa fa-copyright"></i>', 'okul_bilisim_doi_doi_article_doi');
        $rowAction->setAttributes(
            [
                'class' => 'btn btn-primary btn-xs',
                'data-toggle' => 'tooltip',
                'title' => 'Get DOI',
            ]
        );
        $rowAction->setRouteParameters(['id', 'journalId' => $journal->getId()]);

        $rowActions[] = $rowAction;
        $actionColumn->setRowActions($rowActions);
        $event->setGrid($grid);
    }
}
