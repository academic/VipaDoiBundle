<?php

namespace OkulBilisim\OjsDoiBundle\Service;


use Doctrine\ORM\EntityManager;
use Ojs\JournalBundle\Entity\Article;
use Ojs\JournalBundle\Entity\Issue;
use Ojs\JournalBundle\Service\JournalService;
use OkulBilisim\OjsDoiBundle\Entity\CrossrefConfig;
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
        $field = $config->getPostFix();
        if(empty($field)) {
            $field = (new CrossrefConfig())->getPostFix();
        }
        $map = array(
            '%j' => $accessor->getValue($entity, 'journal.id'),
            '%v' => $accessor->getValue($entity, 'issue.volume'),
            '%i' => $accessor->getValue($entity, 'issue.id'),
            '%Y' => $accessor->getValue($entity, 'issue.year'),
            '%a' => $accessor->getValue($entity, 'id'),
        );
        $postFix = str_replace(array_keys($map), array_values($map), $field);

        return $config->getPrefix().'/'.$postFix;
    }
}
