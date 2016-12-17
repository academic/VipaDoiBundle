<?php

namespace Ojs\OjsDoiBundle\Service;


use Ojs\OjsDoiBundle\Entity\CrossrefConfig;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use JMS\Serializer\Exception\LogicException;
use Ojs\CoreBundle\Params\DoiStatuses;
use Ojs\JournalBundle\Entity\Article;
use Ojs\JournalBundle\Service\JournalService;
use Symfony\Component\PropertyAccess\PropertyAccess;

class DoiGenerator
{
    /** @var  EntityManager */
    protected $em;

    /** @var  JournalService */
    protected $journalService;

    /**
     * DoiGenerator constructor.
     * @param EntityManager $em
     * @param JournalService $journalService
     */
    public function __construct(EntityManager $em, JournalService $journalService)
    {
        $this->em               = $em;
        $this->journalService   = $journalService;
    }

    /**
     * @param Article $entity
     * @return string
     */
    public function generate(Article $entity)
    {
        $journal = $this->journalService->getSelectedJournal(false);
        if(!$journal){
            throw new LogicException('Selected journal not found from request');
        }
        $config = $this->em->getRepository(CrossrefConfig::class)->findOneBy(
            array(
                'journal' => $journal
            )
        );
        $accessor = PropertyAccess::createPropertyAccessor();
        $field = $config->getSuffix();
        if (empty($field)) {
            $field = (new CrossrefConfig())->getSuffix();
        }

        $date = $entity->getPubdate() ? $entity->getPubdate() : new \DateTime();
        $map = array(
            '%j' => $journal->getSlug(),
            '%v' => $entity->getIssue() ? $entity->getIssue()->getVolume() : null,
            '%i' => $entity->getIssue() ? $entity->getIssue()->getId() : null,
            '%Y' => $date->format('Y'),
            '%a' => $accessor->getValue($entity, 'id'),
        );
        $postFix = str_replace(array_keys($map), array_values($map), $field);

        return $config->getPrefix().'/'.$postFix;
    }



    /**
     * @return array
     */
    public function generateDoiArticleBarChartData()
    {

        $connectionParams = $this->em->getConnection()->getParams();

        if ($connectionParams['driver'] == 'pdo_sqlite') {
            $sql = 'SELECT count(id) as count , strftime("%m-%Y", created) as month  FROM article WHERE doi IS NOT NULL GROUP BY month';
        }else{
            $sql =  'SELECT count(doi_doi_status.id) as count,date_trunc(\'month\', article.doi_request_time) as month FROM doi_doi_status';
            $sql .= ' INNER JOIN article ON doi_doi_status.article_id = article.id';
            $sql .= ' WHERE article.doi is not null AND article.doi_request_time is not null AND article.doistatus ='.DoiStatuses::VALID;
            $sql .= ' GROUP BY month';
        }
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('count','count');
        $rsm->addScalarResult('month','month');
        $query = $this->em->createNativeQuery($sql, $rsm);
        $results = $query->getResult();

        $doiDataX = ['x'];
        $doiDataCount = ['Doi'];

        foreach($results as $result){
            $doiDataX[] = substr($result['month'], 0, 10);
            $doiDataCount[] = $result['count'];
        }
        return [$doiDataX,$doiDataCount];
    }

    /**
     * @return array
     */
    public function generateDoiArticleMonthlyData()
    {
        $connectionParams = $this->em->getConnection()->getParams();

        if ($connectionParams['driver'] == 'pdo_sqlite') {
            $sql = 'SELECT count(id) as count , strftime("%Y-%m", doi_request_time) as month  FROM article WHERE doi IS NOT NULL GROUP BY month ORDER BY month DESC ';
        }else{
            $sql =  'SELECT count(doi_doi_status.id) as count,date_trunc(\'month\', article.doi_request_time) as month FROM doi_doi_status';
            $sql .= ' INNER JOIN article ON doi_doi_status.article_id = article.id';
            $sql .= ' WHERE article.doi is not null AND article.doi_request_time is not null AND article.doistatus ='.DoiStatuses::VALID;
            $sql .= ' GROUP BY month ORDER BY month DESC';
        }

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('count','count');
        $rsm->addScalarResult('month','month');
        $query = $this->em->createNativeQuery($sql, $rsm);
        $results = $query->getResult();

        return $results;
    }


    /**
     * @return array
     */
    public function generateDoiArticleYearlyData()
    {
        $connectionParams = $this->em->getConnection()->getParams();

        if ($connectionParams['driver'] == 'pdo_sqlite') {
            $sql = 'SELECT count(id) as count , strftime("%Y", doi_request_time) as year FROM article WHERE doi IS NOT NULL GROUP BY year';
        }else{
            $sql =  'SELECT count(doi_doi_status.id) as count,date_trunc(\'year\', article.doi_request_time) as year FROM doi_doi_status';
            $sql .= ' INNER JOIN article ON doi_doi_status.article_id = article.id';
            $sql .= ' WHERE article.doi is not null AND article.doi_request_time is not null AND article.doistatus ='.DoiStatuses::VALID;
            $sql .= ' GROUP BY year ORDER BY year DESC';
        }

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('count','count');
        $rsm->addScalarResult('year','year');
        $query = $this->em->createNativeQuery($sql, $rsm);
        $results = $query->getResult();

        return $results;
    }

}
