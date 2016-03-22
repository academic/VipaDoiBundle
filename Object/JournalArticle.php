<?php

namespace BulutYazilim\OjsDoiBundle\Object;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;

class JournalArticle
{
    /**
     *  abstract_only, bibliographic_record, full_text
     *  @JMS\XmlAttribute
     */
    public $publicationType = "full_text";

    /**
     *  http://www.w3.org/WAI/ER/IG/ert/iso639.htm
     *  @JMS\XmlAttribute
     */
    public $language;

    /**
     * @JMS\XmlMap(entry="title", keyAttribute=null)
     * @JMS\XmlElement(cdata=false)
     * @var ArrayCollection
     */
    public $titles;

    /**
     * @JMS\XmlList(inline = false, entry = "person_name")
     * @var ArrayCollection|PersonName[]
     */
    public $contributors;

    /** @var  PublicationDate */
    public $publicationDate;

    /** @var  Pages */
    public $pages;

    /** @var DoiData */
    public $doiData;

    public function __construct()
    {
        $this->publicationDate = new PublicationDate();
        $this->pages = new Pages();
        $this->titles = new ArrayCollection();
        $this->contributors = new ArrayCollection();
    }
}
