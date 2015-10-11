<?php

namespace OkulBilisim\OjsDoiBundle\Entity;

use Ojs\CoreBundle\Entity\TimestampableTrait;
use Ojs\JournalBundle\Entity\Article;

class DoiStatus
{
    use TimestampableTrait;

    /** @var  integer */
    protected $id;

    /** @var  Article */
    protected $article;

    /** @var  string */
    protected $batchId;

    /** @var  string */
    protected $status;

    /** @var  string */
    protected $description;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Article
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * @param Article $article
     * @return DoiStatus
     */
    public function setArticle(Article $article)
    {
        $this->article = $article;

        return $this;
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
