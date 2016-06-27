<?php

namespace BulutYazilim\OjsDoiBundle\Service;


use BulutYazilim\OjsDoiBundle\Entity\CrossrefConfig;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\Exception\LogicException;
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
}
