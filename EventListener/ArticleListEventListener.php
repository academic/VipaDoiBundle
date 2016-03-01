<?php

namespace OkulBilisim\OjsDoiBundle\EventListener;

use APY\DataGridBundle\Grid\Action\RowAction;
use APY\DataGridBundle\Grid\Column\ActionsColumn;
use APY\DataGridBundle\Grid\Row;
use Ojs\JournalBundle\Event\Article\ArticleEvents;
use Ojs\JournalBundle\Event\ListEvent;
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
            ArticleEvents::LISTED => 'onListInitialized',
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

        $rowAction->manipulateRender(
            function (RowAction $rowAction, Row $row) use ($journal) {
                if (1 === $row->getField('status') && $row->getField('pubdate') >= new \DateTime(
                        '2014-01-01'
                    ) && '' !== $row->getField('issue.translations.title')
                ) {
                    $rowAction->setAttributes(
                        [
                            'class' => 'btn btn-primary btn-xs',
                            'data-toggle' => 'tooltip',
                            'title' => 'Get DOI',
                        ]
                    );
                    $rowAction->setRouteParameters(['id', 'journalId' => $journal->getId()]);

                    return $rowAction;
                }
                return null;
            }
        );

        $rowActions[] = $rowAction;
        $actionColumn->setRowActions($rowActions);


        $event->setGrid($grid);
    }
}
