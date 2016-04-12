<?php

namespace BulutYazilim\OjsDoiBundle\Service;


use BulutYazilim\OjsDoiBundle\Entity\CrossrefConfig;
use Doctrine\ORM\EntityManager;
use Ojs\JournalBundle\Entity\Article;
use Symfony\Component\PropertyAccess\PropertyAccess;

class DoiGenerator
{
    /** @var  EntityManager */
    protected $em;

    /**
     * DoiGenerator constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param Article $entity
     * @return string
     */
    public function generate(Article $entity)
    {

        $config = $this->em->getRepository(CrossrefConfig::class)->findOneBy(
            array(
                'journal' => $entity->getJournal()
            )
        );
        $accessor = PropertyAccess::createPropertyAccessor();
        $field = $config->getSuffix();
        if (empty($field)) {
            $field = (new CrossrefConfig())->getSuffix();
        }

        $date = $entity->getPubdate() ? $entity->getPubdate() : new \DateTime();
        $map = array(
            '%j' => $entity->getJournal()->getSlug(),
            '%v' => $entity->getIssue() ? $entity->getIssue()->getVolume() : null,
            '%i' => $entity->getIssue() ? $entity->getIssue()->getId() : null,
            '%Y' => $date->format('Y'),
            '%a' => $accessor->getValue($entity, 'id'),
        );
        $postFix = str_replace(array_keys($map), array_values($map), $field);

        return $config->getPrefix().'/'.$postFix;
    }
}
