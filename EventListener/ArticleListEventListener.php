<?php

namespace Vipa\DoiBundle\EventListener;

use APY\DataGridBundle\Grid\Action\RowAction;
use APY\DataGridBundle\Grid\Column\ActionsColumn;
use APY\DataGridBundle\Grid\Row;
use Doctrine\Common\Persistence\ObjectManager;
use Vipa\CoreBundle\Params\ArticleStatuses;
use Vipa\CoreBundle\Params\DoiStatuses;
use Vipa\JournalBundle\Event\Article\ArticleEvents;
use Vipa\JournalBundle\Event\ListEvent;
use Vipa\JournalBundle\Service\JournalService;
use Vipa\JournalBundle\Validator\Constraints\ArticleStatus;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ArticleListEventListener implements EventSubscriberInterface
{
    /** @var JournalService */
    private $journalService;

    /** @var ObjectManager */
    private $em;

    /**
     * ArticleListEventListener constructor.
     * @param JournalService $journalService
     * @param ObjectManager $em
     */
    public function __construct(JournalService $journalService, ObjectManager $em)
    {
        $this->journalService = $journalService;
        $this->em = $em;
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
        $crossrefConfig = $this->em->getRepository('VipaDoiBundle:CrossrefConfig')->findOneBy(array('journal' => $journal));
        if(!$crossrefConfig || !$crossrefConfig->isValid()) {
            return;
        }

        $grid = $event->getGrid();
        /** @var ActionsColumn $actionColumn */
        $actionColumn = $grid->getColumn("actions");
        $rowActions = $actionColumn->getRowActions();

        $rowAction = new RowAction('DOI', 'bulut_yazilim_doi_doi_article_doi');

        $rowAction->addRouteParameters([
            'journalId' => $journal->getId(),
            'id'
        ]);
        $rowAction->manipulateRender(
            function (RowAction $rowAction, Row $row) use ($journal) {
                if (
                    $row->getField('pubdate') >= new \DateTime('2014-01-01')
                    && $row->getField('doiStatus') !== DoiStatuses::VALID
                    && $row->getField('status') !== ArticleStatuses::STATUS_REJECTED) {
                    $rowAction->setAttributes(
                        [
                            'class' => 'btn btn-primary btn-xs',
                            'data-toggle' => 'tooltip',
                            'title' => 'Get DOI',
                        ]
                    );
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
