<?php
namespace BulutYazilim\OjsDoiBundle\Controller;
use Ojs\CoreBundle\Controller\OjsController as Controller;
use Doctrine\ORM\Query\ResultSetMapping;
use Ojs\CoreBundle\Params\DoiStatuses;

class DoiStatsController extends Controller
{
    public function doiDetailAction($year, $month)
    {
        $em = $this->getDoctrine()->getManager();

        $sql = 'SELECT id,doi,doi_request_time FROM article WHERE doi is not null AND doistatus = '.DoiStatuses::VALID.' AND EXTRACT(YEAR FROM doi_request_time) ='.$year;

        if (!empty($month)){
            $sql .= 'AND EXTRACT(MONTH FROM doi_request_time) ='.$month;
        }

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
}