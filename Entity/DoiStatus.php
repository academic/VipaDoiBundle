<?php

namespace Ojs\OjsDoiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ojs\JournalBundle\Entity\ArticleTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="doi_doi_status")
 * @Gedmo\Loggable
 */
class DoiStatus
{
    use TimestampableEntity;
    use ArticleTrait;

    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $batchId;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Gedmo\Versioned()
     */
    protected $status;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $description;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getBatchId()
    {
        return $this->batchId;
    }

    /**
     * @param string $batchId
     * @return DoiStatus
     */
    public function setBatchId($batchId)
    {
        $this->batchId = $batchId;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return DoiStatus
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return DoiStatus
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

}
