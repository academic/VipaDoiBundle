<?php

namespace BulutYazilim\OjsDoiBundle\Service;


use Doctrine\ORM\EntityManager;
use Ojs\JournalBundle\Entity\Article;
use Ojs\JournalBundle\Service\JournalService;
use BulutYazilim\OjsDoiBundle\Entity\CrossrefConfig;
use Symfony\Component\PropertyAccess\PropertyAccess;

class DoiGenerator
{
    /** @var  JournalService */
    protected $journalService;

    /** @var  EntityManager */
    protected $em;

    /**
     * DoiGenerator constructor.
     * @param JournalService $journalService
     * @param EntityManager $em
     */
    public function __construct(JournalService $journalService, EntityManager $em)
    {
        $this->journalService = $journalService;
        $this->em = $em;
    }

    /**
     * @param Article $entity
     * @return string
     */
    public function generate(Article $entity) {

        $config = $this->em->getRepository('OjsDoiBundle:CrossrefConfig')->findOneBy(array(
            'journal' => $this->journalService->getSelectedJournal()
        ));
        $accessor = PropertyAccess::createPropertyAccessor();
        $field = $config->getSuffix();
        if(empty($field)) {
            $field = (new CrossrefConfig())->getSuffix();
        }

        $map = array(
            '%j' => $entity->getSlug(),
            '%v' => $entity->getIssue()?$entity->getIssue()->getVolume():null,
            '%i' => $entity->getIssue()?$entity->getIssue()->getId():null,
            '%Y' => $entity->getIssue()?$entity->getIssue()->getYear():null,
            '%a' => $accessor->getValue($entity, 'id'),
        );
        $postFix = str_replace(array_keys($map), array_values($map), $field);

        return $config->getPrefix().'/'.$postFix;
    }
}
