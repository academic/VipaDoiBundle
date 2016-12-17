<?php
namespace Ojs\OjsDoiBundle\Controller;
use Ojs\CoreBundle\Controller\OjsController as Controller;
use Doctrine\ORM\Query\ResultSetMapping;
use Ojs\CoreBundle\Params\DoiStatuses;

class DoiStatsController extends Controller
{
    public function doiDetailAction($year, $month)
    {
        $em = $this->getDoctrine()->getManager();
        
        $sql =  'SELECT article.id, article.doi,article.doi_request_time FROM doi_doi_status';
        $sql .= ' INNER JOIN article ON doi_doi_status.article_id = article.id';
        $sql .= ' WHERE article.doi is not null AND article.doi_request_time is not null AND article.doistatus ='.DoiStatuses::VALID;
        $sql .= ' AND EXTRACT(YEAR FROM article.doi_request_time) ='.$year;

        if (!empty($month)){
            $sql .= 'AND EXTRACT(MONTH FROM article.doi_request_time) ='.$month;
        }

        $sql .= ' ORDER BY article.doi_request_time DESC';

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id','id');
        $rsm->addScalarResult('doi','doi');
        $rsm->addScalarResult('doi_request_time','time');
        $query = $em->createNativeQuery($sql, $rsm);
        $results = $query->getResult();

        return $this->render('OjsDoiBundle:Admin:doi_list.html.twig',[
            'results' => $results
        ]);
    }

    public function journalDoiDetailAction()
    {
        $em = $this->getDoctrine()->getManager();

        $sql =  'SELECT journal.id, count(article.id) as count FROM doi_doi_status';
        $sql .= ' INNER JOIN article ON doi_doi_status.article_id = article.id';
        $sql .= ' INNER JOIN journal ON article.journal_id = journal.id';
        $sql .= ' WHERE article.doi is not null AND article.doi_request_time is not null AND article.doistatus ='.DoiStatuses::VALID;
        $sql .= ' GROUP BY journal.id';

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id','id');
        $rsm->addScalarResult('count','count');
        $query = $em->createNativeQuery($sql, $rsm);
        $results = $query->getResult();

        return $this->render('OjsDoiBundle:Admin:journal_list.html.twig',[
            'results' => $results
        ]);
    }
}